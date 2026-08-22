<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\PaymentMethod;
use App\Models\Provider;
use App\Models\PaymentTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PaymentGatewayTest extends TestCase
{
    use RefreshDatabase;

    private function provider(): Provider
    {
        return Provider::create([
            'name' => 'Gateway Uji',
            'type' => 'payment',
            'api_format' => 'rest_json',
            'base_url' => 'https://gateway.test/v1/charges',
            'api_key' => 'secret-key-123',
            'config' => [
                'currency' => 'IDR',
                'payload_map' => ['order_id' => 'transaction_id', 'gross_amount' => 'amount'],
                'external_id_path' => 'data.id',
                'checkout_url_path' => 'data.checkout_url',
                'callback_external_id_path' => 'order_id',
                'callback_status_path' => 'transaction_status',
                'paid_values' => ['settlement'],
                'payment_method_id' => null,
                'require_signature' => true,
                'signature_header' => 'X-Signature',
                'event_id_header' => 'X-Event-Id',
            ],
            'is_active' => true,
        ]);
    }

    private function invoice(): Invoice
    {
        $customer = \App\Models\Customer::create(['name' => 'Pembayar', 'email' => 'pay@test.local', 'phone' => '0811', 'customer_type' => 'individual', 'verification_status' => 'verified', 'is_active' => true]);

        return Invoice::create(['customer_id' => $customer->id, 'type' => 'rental', 'subtotal' => 1000000, 'total_amount' => 1000000, 'balance_due' => 1000000, 'status' => 'issued']);
    }

    public function test_create_transaction_maps_payload_and_stores_checkout_url(): void
    {
        Http::fake([
            'https://gateway.test/v1/charges' => Http::response(['data' => ['id' => 'EXT-99', 'checkout_url' => 'https://pay.test/EXT-99']], 200),
        ]);

        $tx = app(\App\Services\PaymentGatewayService::class)->create($this->provider(), $this->invoice());

        $this->assertSame('pending', $tx->status);
        $this->assertSame('EXT-99', $tx->external_id);
        $this->assertSame(1000000.0, (float) $tx->amount);
        $this->assertSame('https://pay.test/EXT-99', $tx->checkout_url);
        $this->assertSame($tx->public_id, data_get($tx->request_payload, 'order_id'));
    }

    public function test_callback_with_valid_hmac_signature_marks_invoice_paid(): void
    {
        PaymentMethod::create(['name' => 'Transfer Bank', 'code' => 'BANK', 'type' => 'bank_transfer', 'is_active' => true]);
        $method = PaymentMethod::where('code', 'BANK')->first();
        $provider = $this->provider();
        $provider->update(['config' => array_merge($provider->config, ['payment_method_id' => $method->id])]);

        $invoice = $this->invoice();
        $tx = PaymentTransaction::create(['public_id' => '11111111-2222-3333-4444-555555555555', 'provider_id' => $provider->id, 'invoice_id' => $invoice->id, 'customer_id' => $invoice->customer_id, 'amount' => 500000, 'currency' => 'IDR', 'status' => 'pending']);
        $tx->update(['external_id' => 'EXT-77']);

        $payload = ['order_id' => 'EXT-77', 'transaction_status' => 'settlement'];
        $body = json_encode($payload);
        $signature = hash_hmac('sha256', $body, 'secret-key-123');

        $this->postJson(route('payments.callback', $provider), $payload, ['X-Signature' => $signature, 'X-Event-Id' => 'EVT-1'])
            ->assertOk()
            ->assertJsonPath('ok', true);

        $this->assertDatabaseHas('payment_transactions', ['id' => $tx->id, 'status' => 'paid']);
        $this->assertDatabaseHas('payments', ['invoice_id' => $tx->invoice_id, 'status' => 'verified', 'amount' => 500000]);
    }

    public function test_callback_with_invalid_signature_is_rejected(): void
    {
        $provider = $this->provider();
        $payload = ['order_id' => 'X', 'transaction_status' => 'settlement'];

        $this->postJson(route('payments.callback', $provider), $payload, ['X-Signature' => 'salah'])
            ->assertStatus(401);
    }

    public function test_duplicate_callback_event_is_idempotent(): void
    {
        PaymentMethod::create(['name' => 'Transfer Bank', 'code' => 'BNK2', 'type' => 'bank_transfer', 'is_active' => true]);
        $method = PaymentMethod::where('code', 'BNK2')->first();
        $provider = $this->provider();
        $provider->update(['config' => array_merge($provider->config, ['payment_method_id' => $method->id])]);
        $invoice = $this->invoice();
        $tx = PaymentTransaction::create(['public_id' => 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee', 'provider_id' => $provider->id, 'invoice_id' => $invoice->id, 'customer_id' => $invoice->customer_id, 'amount' => 300000, 'currency' => 'IDR', 'status' => 'pending']);
        $tx->update(['external_id' => 'EXT-88']);

        $payload = ['order_id' => 'EXT-88', 'transaction_status' => 'settlement'];
        $headers = ['X-Signature' => hash_hmac('sha256', json_encode($payload), 'secret-key-123'), 'X-Event-Id' => 'EVT-DUP'];

        $this->postJson(route('payments.callback', $provider), $payload, $headers)->assertOk();
        $firstCount = \App\Models\Payment::count();

        $this->postJson(route('payments.callback', $provider), $payload, $headers)->assertOk();

        $this->assertSame($firstCount, \App\Models\Payment::count());
    }
}

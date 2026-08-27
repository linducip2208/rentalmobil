<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\PaymentTransaction;
use App\Models\Provider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class PaymentGatewayService
{
    public function create(Provider $provider, Invoice $invoice): PaymentTransaction
    {
        abort_unless($provider->type === 'payment' && $provider->is_active, 422, 'Provider pembayaran tidak aktif.');
        $config = $provider->config ?? [];
        $tx = PaymentTransaction::create(['public_id' => (string) Str::uuid(), 'provider_id' => $provider->id, 'invoice_id' => $invoice->id, 'customer_id' => $invoice->customer_id, 'amount' => $invoice->balance_due, 'currency' => $config['currency'] ?? 'IDR', 'status' => 'created']);
        $payload = ['transaction_id' => $tx->public_id, 'amount' => (float) $tx->amount, 'currency' => $tx->currency, 'customer_name' => $invoice->customer->name, 'customer_email' => $invoice->customer->email, 'callback_url' => route('payments.callback', $provider), 'return_url' => route('portal.invoices')];
        $payload = $this->map($payload, $config['payload_map'] ?? []);
        $request = Http::withHeaders($this->headers($provider))->timeout((int) ($config['timeout_seconds'] ?? 30));
        $response = $provider->api_format === 'rest_form' ? $request->asForm()->post($provider->base_url, $payload) : $request->asJson()->post($provider->base_url, $payload);
        $response->throw();
        $body = $response->json() ?: [];
        $tx->update(['external_id' => data_get($body, $config['external_id_path'] ?? 'id'), 'checkout_url' => data_get($body, $config['checkout_url_path'] ?? 'checkout_url'), 'request_payload' => $payload, 'response_payload' => $body, 'status' => 'pending']);

        return $tx->fresh();
    }

    public function callback(Provider $provider, array $payload, ?string $eventId): PaymentTransaction
    {
        $config = $provider->config ?? [];
        $external = data_get($payload, $config['callback_external_id_path'] ?? 'id');
        $tx = PaymentTransaction::where('provider_id', $provider->id)->where(fn ($q) => $q->where('external_id', $external)->orWhere('public_id', $external))->firstOrFail();
        if ($eventId && PaymentTransaction::where('callback_event_id', $eventId)->exists()) {
            return $tx;
        }$status = (string) data_get($payload, $config['callback_status_path'] ?? 'status');
        $paidValues = array_map('strval', (array) ($config['paid_values'] ?? ['paid', 'success', 'settled']));
        if (in_array($status, $paidValues, true) && $tx->status !== 'paid') {
            $tx->update(['status' => 'paid', 'paid_at' => now(), 'callback_event_id' => $eventId, 'response_payload' => $payload]);
            app(PaymentService::class)->recordAndVerifyGatewayPayment($tx);
        } else {
            $tx->update(['response_payload' => $payload, 'callback_event_id' => $eventId]);
        }

return $tx->fresh();
    }

    private function headers(Provider $p): array
    {
        $h = $p->extra_headers ?? [];
        if ($p->api_key) {
            $h[($p->config['auth_header'] ?? 'Authorization')] = trim(($p->config['auth_scheme'] ?? 'Bearer').' '.$p->api_key);
        }

return $h;
    }

    private function map(array $payload, mixed $map): array
    {
        if (is_string($map)) {
            $map = json_decode($map, true) ?: [];
        }if (! $map) {
            return $payload;
        }$out = [];
        foreach ($map as $target => $source) {
            $out[$target] = data_get($payload,$source,$source);
        }

return $out;
    }
}

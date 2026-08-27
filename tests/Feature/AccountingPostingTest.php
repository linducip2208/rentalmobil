<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Services\AccountingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountingPostingTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_posting_is_balanced_and_idempotent(): void
    {
        $customer = Customer::create([
            'name' => 'Pelanggan Akuntansi',
            'email' => 'accounting@example.test',
            'phone' => '081234567890',
            'customer_type' => 'individual',
            'verification_status' => 'verified',
            'is_active' => true,
        ]);
        $method = PaymentMethod::create([
            'name' => 'Transfer Test',
            'code' => 'transfer-test',
            'type' => 'bank_transfer',
            'is_active' => true,
        ]);
        $invoice = Invoice::create([
            'customer_id' => $customer->id,
            'type' => 'rental',
            'subtotal' => 500000,
            'total_amount' => 500000,
            'balance_due' => 500000,
            'status' => 'issued',
        ]);
        $payment = Payment::create([
            'invoice_id' => $invoice->id,
            'customer_id' => $customer->id,
            'payment_method_id' => $method->id,
            'amount' => 500000,
            'payment_date' => today(),
            'status' => 'verified',
        ]);

        $service = app(AccountingService::class);
        $first = $service->recordPayment($payment);
        $second = $service->recordPayment($payment);

        $this->assertTrue($first->is($second));
        $this->assertSame(1, JournalEntry::where('posting_key', "payment:{$payment->id}")->count());
        $this->assertSame('500000.00', $first->fresh()->total_debit);
        $this->assertSame($first->total_debit, $first->total_credit);
        $this->assertEquals(500000.0, (float) $first->lines()->sum('debit'));
        $this->assertEquals(500000.0, (float) $first->lines()->sum('credit'));
    }
}

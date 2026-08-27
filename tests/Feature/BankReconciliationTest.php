<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\BankReconciliationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BankReconciliationTest extends TestCase
{
    use RefreshDatabase;

    private function csv(): string
    {
        return implode("\n", [
            'Tanggal Transaksi;Keterangan;Mutasi Masuk;Mutasi Keluar;No. Referensi',
            '25/08/2026;TRANSFER MASUK TRX-777;1.500.000,00;;TRX-777',
            '26/08/2026;TRANSFER MASUK TANPA REF;500.000,00;;',
            '26/08/2026;BEBAN LISTRIK KANTOR;;750.000,00;',
            'Saldo Awal;;;10.000.000,00;',
        ]);
    }

    private function invoice(float $amount): Invoice
    {
        $customer = Customer::create(['name' => 'Recon User', 'email' => 'recon'.uniqid().'@test.local', 'phone' => '0817', 'customer_type' => 'individual', 'verification_status' => 'verified', 'is_active' => true]);

        return Invoice::create(['customer_id' => $customer->id, 'type' => 'rental', 'subtotal' => $amount, 'total_amount' => $amount, 'balance_due' => $amount, 'status' => 'issued']);
    }

    public function test_parse_and_import_indonesian_csv(): void
    {
        $import = app(BankReconciliationService::class)->import(null, $this->csv(), 'mutasi.csv', 1);

        $this->assertSame(3, $import->total_lines);
        $this->assertSame('ready', $import->status);
        $this->assertSame('2026-08-25', $import->period_start->toDateString());

        $first = $import->lines()->orderBy('transaction_date')->first();
        $this->assertSame(1500000.0, (float) $first->amount_in);
        $this->assertSame('TRX-777', $first->reference);
    }

    public function test_auto_match_by_exact_reference_then_verify(): void
    {
        $invoice = $this->invoice(1500000);
        Payment::create(['invoice_id' => $invoice->id, 'rental_order_id' => null, 'customer_id' => $invoice->customer_id, 'amount' => 1500000, 'payment_date' => today(), 'reference_number' => 'TRX-777', 'status' => 'pending']);

        $import = app(BankReconciliationService::class)->import(null, $this->csv(), 'mutasi.csv', 1);
        $matched = app(BankReconciliationService::class)->autoMatch($import);

        $this->assertSame(1, $matched);

        $line = $import->lines()->where('reference', 'TRX-777')->first();
        $this->assertSame('matched', $line->match_status);
        $this->assertEquals(1.0, (float) $line->match_confidence);

        $verified = app(BankReconciliationService::class)->verifyMatched($import, 1);
        $this->assertSame(1, $verified);
        $this->assertSame('posted', $import->fresh()->status);
        $this->assertContains($invoice->fresh()->status, ['paid', 'partially_paid']);
    }

    public function test_fuzzy_match_same_amount_near_date(): void
    {
        $invoice = $this->invoice(500000);
        Payment::create(['invoice_id' => $invoice->id, 'rental_order_id' => null, 'customer_id' => $invoice->customer_id, 'amount' => 500000, 'payment_date' => today()->addDay(), 'reference_number' => 'REF-LAIN-999', 'status' => 'pending']);

        $import = app(BankReconciliationService::class)->import(null, $this->csv(), 'mutasi.csv', 1);
        app(BankReconciliationService::class)->autoMatch($import);

        $line = $import->lines()->where('description', 'like', '%TANPA REF%')->first();
        $this->assertSame('matched', $line->match_status);
        $this->assertNotNull($line->matched_payment_id);
        $this->assertTrue((float) $line->match_confidence < 1.0);
    }

    public function test_amount_mismatch_is_not_matched(): void
    {
        $invoice = $this->invoice(999999);
        Payment::create(['invoice_id' => $invoice->id, 'rental_order_id' => null, 'customer_id' => $invoice->customer_id, 'amount' => 999999, 'payment_date' => today(), 'reference_number' => 'BEDA-NOMINAL', 'status' => 'pending']);

        $import = app(BankReconciliationService::class)->import(null, $this->csv(), 'mutasi.csv', 1);
        app(BankReconciliationService::class)->autoMatch($import);

        $this->assertSame(0, $import->lines()->where('match_status', 'matched')->whereHas('matchedPayment', fn ($q) => $q->where('reference_number', 'BEDA-NOMINAL'))->count());
    }
}

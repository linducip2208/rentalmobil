<?php

namespace App\Services;

use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\SupplierInvoice;
use App\Models\SupplierPayment;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SupplierPayableService
{
    public function __construct(private readonly PeriodClosingService $periods) {}

    public function post(SupplierInvoice $invoice): JournalEntry
    {
        return DB::transaction(function () use ($invoice) {
            $invoice = SupplierInvoice::query()->lockForUpdate()->findOrFail($invoice->id);
            if ($invoice->status !== 'draft') {
                if ($entry = JournalEntry::where('posting_key', "supplier-invoice:{$invoice->id}")->first()) {
                    return $entry;
                } throw new RuntimeException('Supplier bill bukan draft.');
            } $this->periods->assertPostingAllowed($invoice->invoice_date);
            $inventory = $this->account('1300');
            $inputTax = $this->account('1301', false);
            $ap = $this->account('2100');
            $debits = [[$inventory, (float) $invoice->subtotal - (float) $invoice->discount_amount, 'Persediaan dari supplier']];
            if ((float) $invoice->tax_amount > 0) {
                if (! $inputTax) {
                    throw new RuntimeException('Akun pajak masukan belum dikonfigurasi.');
                }$debits[] = [$inputTax, (float) $invoice->tax_amount, 'Pajak masukan'];
            } $debitTotal = array_sum(array_column($debits, 1));
            if (abs($debitTotal - (float) $invoice->total) > 0.01) {
                throw new RuntimeException('Total supplier bill tidak seimbang.');
            } $entry = JournalEntry::create(['posting_key' => "supplier-invoice:{$invoice->id}", 'location_id' => $invoice->location_id, 'date' => $invoice->invoice_date, 'description' => "Supplier bill {$invoice->bill_number}", 'reference_type' => $invoice->getMorphClass(), 'reference_id' => $invoice->id, 'total_debit' => $invoice->total, 'total_credit' => $invoice->total, 'status' => 'posted', 'posted_by' => auth()->id()]);
            foreach ($debits as [$account,$amount,$description]) {
                JournalLine::create(['journal_entry_id' => $entry->id, 'account_id' => $account, 'description' => $description, 'debit' => $amount, 'credit' => 0]);
            } JournalLine::create(['journal_entry_id' => $entry->id, 'account_id' => $ap, 'description' => 'Hutang usaha supplier', 'debit' => 0, 'credit' => $invoice->total]);
            $invoice->update(['status' => 'posted', 'posted_by' => auth()->id(), 'posted_at' => now()]);

            return $entry;
        }, 3);
    }

    public function pay(SupplierInvoice $invoice, float $amount, ?int $bankAccountId = null, ?string $reference = null): SupplierPayment
    {
        return DB::transaction(function () use ($invoice, $amount, $bankAccountId, $reference) {
            $invoice = SupplierInvoice::query()->lockForUpdate()->findOrFail($invoice->id);
            if (! in_array($invoice->status, ['posted', 'partial', 'overdue'], true)) {
                throw new RuntimeException('Supplier bill belum diposting atau sudah lunas.');
            } if ($amount <= 0 || $amount > $invoice->outstanding_amount + 0.01) {
                throw new RuntimeException('Nominal pembayaran supplier tidak valid.');
            } $this->periods->assertPostingAllowed(now());
            $payment = SupplierPayment::create(['supplier_invoice_id' => $invoice->id, 'supplier_id' => $invoice->supplier_id, 'location_id' => $invoice->location_id, 'bank_account_id' => $bankAccountId, 'payment_date' => now(), 'amount' => $amount, 'reference' => $reference, 'status' => 'posted', 'created_by' => auth()->id()]);
            $ap = $this->account('2100');
            $cash = $this->account('1101');
            $entry = JournalEntry::create(['posting_key' => "supplier-payment:{$payment->id}", 'location_id' => $invoice->location_id, 'date' => $payment->payment_date, 'description' => "Supplier payment {$payment->payment_number}", 'reference_type' => $payment->getMorphClass(), 'reference_id' => $payment->id, 'total_debit' => $amount, 'total_credit' => $amount, 'status' => 'posted', 'posted_by' => auth()->id()]);
            JournalLine::create(['journal_entry_id' => $entry->id, 'account_id' => $ap, 'description' => 'Pelunasan hutang supplier', 'debit' => $amount, 'credit' => 0]);
            JournalLine::create(['journal_entry_id' => $entry->id, 'account_id' => $cash, 'description' => 'Kas/bank keluar', 'debit' => 0, 'credit' => $amount]);
            $paid = round((float) $invoice->paid_amount + $amount, 2);
            $invoice->update(['paid_amount' => $paid, 'status' => $paid + 0.01 >= (float) $invoice->total ? 'paid' : 'partial']);

            return $payment;
        }, 3);
    }

    public function aging(?int $locationId = null): array
    {
        $query = SupplierInvoice::query()->with('supplier')->whereIn('status', ['posted', 'partial', 'overdue'])->whereColumn('paid_amount', '<', 'total');
        if ($locationId) {
            $query->where('location_id', $locationId);
        }

        return $query->get()->groupBy->aging_bucket->map(fn ($rows) => ['count' => $rows->count(), 'outstanding' => round($rows->sum->outstanding_amount, 2)])->all();
    }

    private function account(string $code, bool $required = true): ?int
    {
        $id = ChartOfAccount::where('code', $code)->value('id');
        if ($required && ! $id) {
            throw new RuntimeException("Akun {$code} belum dikonfigurasi.");
        }

        return $id;
    }
}

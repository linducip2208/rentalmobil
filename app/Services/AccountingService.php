<?php

namespace App\Services;

use App\Models\ChartOfAccount;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\Payment;
use App\Models\RentalOrder;
use Carbon\Carbon;
use RuntimeException;

class AccountingService
{
    protected ?int $cashAccountId = null;

    protected ?int $rentalRevenueAccountId = null;

    protected ?int $accountsReceivableId = null;

    protected ?int $lateFeeRevenueId = null;

    protected ?int $depositLiabilityId = null;

    protected ?int $damageRevenueId = null;

    public function __construct()
    {
        $this->cashAccountId = $this->findAccountByCode('1101');
        $this->rentalRevenueAccountId = $this->findAccountByCode('4101');
        $this->accountsReceivableId = $this->findAccountByCode('1102');
        $this->lateFeeRevenueId = $this->findAccountByCode('4102');
        $this->depositLiabilityId = $this->findAccountByCode('2101');
        $this->damageRevenueId = $this->findAccountByCode('4103');
    }

    public function recordRentalIncome(RentalOrder $order): JournalEntry
    {
        $totalAmount = (float) $order->final_amount;
        $taxAmount = (float) $order->tax_total;
        $lateFee = (float) $order->late_fee;
        $damageFee = (float) $order->damage_fee;
        $revenueAmount = round($totalAmount - $taxAmount - $lateFee - $damageFee, 2);

        if ($revenueAmount < 0) {
            throw new RuntimeException('Komponen jurnal rental melebihi total transaksi. Periksa pajak dan biaya tambahan.');
        }

        if ($existing = $this->existingPosting("rental-income:{$order->id}")) {
            return $existing;
        }

        $this->requireAccounts([
            'accounts receivable' => $this->accountsReceivableId,
            'rental revenue' => $this->rentalRevenueAccountId,
            'late fee revenue' => $lateFee > 0 ? $this->lateFeeRevenueId : 1,
            'damage revenue' => $damageFee > 0 ? $this->damageRevenueId : 1,
        ]);

        $entry = JournalEntry::create([
            'posting_key' => "rental-income:{$order->id}",
            'date' => now()->toDateString(),
            'description' => "Rental income - {$order->order_number}",
            'reference_type' => RentalOrder::class,
            'reference_id' => $order->id,
            'total_debit' => $totalAmount,
            'total_credit' => $totalAmount,
            'status' => 'posted',
            'posted_by' => auth()->id(),
        ]);

        JournalLine::create([
            'journal_entry_id' => $entry->id,
            'account_id' => $this->accountsReceivableId,
            'description' => "AR - {$order->order_number}",
            'debit' => $totalAmount,
            'credit' => 0,
        ]);

        JournalLine::create([
            'journal_entry_id' => $entry->id,
            'account_id' => $this->rentalRevenueAccountId,
            'description' => "Rental revenue - {$order->order_number}",
            'debit' => 0,
            'credit' => $revenueAmount,
        ]);

        if ($taxAmount > 0) {
            $taxAccountId = $this->findAccountByCode('2102') ?? $this->findAccountByType('liability');
            if ($taxAccountId) {
                JournalLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id' => $taxAccountId,
                    'description' => "PPN collected - {$order->order_number}",
                    'debit' => 0,
                    'credit' => $taxAmount,
                ]);
            }
        }

        if ($lateFee > 0) {
            JournalLine::create([
                'journal_entry_id' => $entry->id,
                'account_id' => $this->lateFeeRevenueId,
                'description' => "Late fee revenue - {$order->order_number}",
                'debit' => 0,
                'credit' => $lateFee,
            ]);
        }

        if ($damageFee > 0) {
            JournalLine::create([
                'journal_entry_id' => $entry->id,
                'account_id' => $this->damageRevenueId,
                'description' => "Damage fee revenue - {$order->order_number}",
                'debit' => 0,
                'credit' => $damageFee,
            ]);
        }

        return $entry;
    }

    public function recordPayment(Payment $payment): JournalEntry
    {
        if ($existing = $this->existingPosting("payment:{$payment->id}")) {
            return $existing;
        }

        $this->requireAccounts(['cash/bank' => $this->cashAccountId, 'accounts receivable' => $this->accountsReceivableId]);
        $amount = (float) $payment->amount;
        $entry = JournalEntry::create([
            'posting_key' => "payment:{$payment->id}",
            'date' => $payment->payment_date ?? now()->toDateString(),
            'description' => "Payment received - {$payment->payment_number}",
            'reference_type' => Payment::class,
            'reference_id' => $payment->id,
            'total_debit' => $amount,
            'total_credit' => $amount,
            'status' => 'posted',
            'posted_by' => auth()->id(),
        ]);

        JournalLine::create([
            'journal_entry_id' => $entry->id,
            'account_id' => $this->cashAccountId,
            'description' => "Cash received - {$payment->payment_number}",
            'debit' => $amount,
            'credit' => 0,
        ]);

        JournalLine::create([
            'journal_entry_id' => $entry->id,
            'account_id' => $this->accountsReceivableId,
            'description' => "AR payment - {$payment->payment_number}",
            'debit' => 0,
            'credit' => $amount,
        ]);

        return $entry;
    }

    public function recordExpense(Expense $expense): JournalEntry
    {
        if ($existing = $this->existingPosting("expense:{$expense->id}")) {
            return $existing;
        }

        $amount = (float) $expense->amount;

        $expenseAccountId = $this->findExpenseAccountForCategory($expense->expense_category_id)
            ?? $this->findAccountByType('expense');
        $this->requireAccounts(['cash/bank' => $this->cashAccountId, 'expense' => $expenseAccountId]);

        $entry = JournalEntry::create([
            'posting_key' => "expense:{$expense->id}",
            'date' => $expense->expense_date,
            'description' => "Expense - {$expense->title} ({$expense->expense_number})",
            'reference_type' => Expense::class,
            'reference_id' => $expense->id,
            'total_debit' => $amount,
            'total_credit' => $amount,
            'status' => 'posted',
            'posted_by' => auth()->id(),
        ]);

        JournalLine::create([
            'journal_entry_id' => $entry->id,
            'account_id' => $expenseAccountId,
            'description' => "Expense - {$expense->title}",
            'debit' => $amount,
            'credit' => 0,
        ]);

        JournalLine::create([
            'journal_entry_id' => $entry->id,
            'account_id' => $this->cashAccountId,
            'description' => "Cash paid - {$expense->expense_number}",
            'debit' => 0,
            'credit' => $amount,
        ]);

        return $entry;
    }

    public function recordDepositReceived(RentalOrder $order): ?JournalEntry
    {
        $depositAmount = (float) $order->deposit_amount;

        if ($depositAmount <= 0) {
            return null;
        }

        if ($existing = $this->existingPosting("deposit-received:{$order->id}")) {
            return $existing;
        }

        $this->requireAccounts(['cash/bank' => $this->cashAccountId, 'customer deposit liability' => $this->depositLiabilityId]);

        $entry = JournalEntry::create([
            'posting_key' => "deposit-received:{$order->id}",
            'date' => now()->toDateString(),
            'description' => "Deposit received - {$order->order_number}",
            'reference_type' => RentalOrder::class,
            'reference_id' => $order->id,
            'total_debit' => $depositAmount,
            'total_credit' => $depositAmount,
            'status' => 'posted',
            'posted_by' => auth()->id(),
        ]);

        JournalLine::create([
            'journal_entry_id' => $entry->id,
            'account_id' => $this->cashAccountId,
            'description' => "Cash deposit - {$order->order_number}",
            'debit' => $depositAmount,
            'credit' => 0,
        ]);

        JournalLine::create([
            'journal_entry_id' => $entry->id,
            'account_id' => $this->depositLiabilityId,
            'description' => "Deposit liability - {$order->order_number}",
            'debit' => 0,
            'credit' => $depositAmount,
        ]);

        return $entry;
    }

    public function recordDepositRefund(RentalOrder $order, float $refundAmount): ?JournalEntry
    {
        if ($refundAmount <= 0) {
            return null;
        }

        $postingKey = 'deposit-refund:'.$order->id.':'.number_format($refundAmount, 2, '.', '');
        if ($existing = $this->existingPosting($postingKey)) {
            return $existing;
        }

        $this->requireAccounts(['cash/bank' => $this->cashAccountId, 'customer deposit liability' => $this->depositLiabilityId]);

        $entry = JournalEntry::create([
            'posting_key' => $postingKey,
            'date' => now()->toDateString(),
            'description' => "Deposit refund - {$order->order_number}",
            'reference_type' => RentalOrder::class,
            'reference_id' => $order->id,
            'total_debit' => $refundAmount,
            'total_credit' => $refundAmount,
            'status' => 'posted',
            'posted_by' => auth()->id(),
        ]);

        JournalLine::create([
            'journal_entry_id' => $entry->id,
            'account_id' => $this->depositLiabilityId,
            'description' => "Deposit liability release - {$order->order_number}",
            'debit' => $refundAmount,
            'credit' => 0,
        ]);

        JournalLine::create([
            'journal_entry_id' => $entry->id,
            'account_id' => $this->cashAccountId,
            'description' => "Cash refund - {$order->order_number}",
            'debit' => 0,
            'credit' => $refundAmount,
        ]);

        return $entry;
    }

    public function getAccountBalance(int $accountId, ?Carbon $startDate = null, ?Carbon $endDate = null): float
    {
        $account = ChartOfAccount::findOrFail($accountId);

        $query = JournalLine::where('account_id', $accountId)
            ->whereHas('journalEntry', function ($q) use ($startDate, $endDate) {
                $q->where('status', 'posted');

                if ($startDate) {
                    $q->where('date', '>=', $startDate->toDateString());
                }
                if ($endDate) {
                    $q->where('date', '<=', $endDate->toDateString());
                }
            });

        $debits = (float) $query->sum('debit');
        $credits = (float) $query->sum('credit');

        if ($account->normal_balance === 'debit') {
            return round($debits - $credits, 2);
        }

        return round($credits - $debits, 2);
    }

    public function getTrialBalance(?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $accounts = ChartOfAccount::active()->get();

        $trialBalance = $accounts->map(function ($account) use ($startDate, $endDate) {
            $balance = $this->getAccountBalance($account->id, $startDate, $endDate);

            return [
                'code' => $account->code,
                'name' => $account->name,
                'type' => $account->type,
                'normal_balance' => $account->normal_balance,
                'balance' => $balance,
            ];
        })->filter(fn ($a) => $a['balance'] != 0);

        $totalDebits = $trialBalance->where('normal_balance', 'debit')->sum('balance');
        $totalCredits = $trialBalance->where('normal_balance', 'credit')->sum('balance');

        return [
            'accounts' => $trialBalance->values(),
            'total_debits' => round($totalDebits, 2),
            'total_credits' => round($totalCredits, 2),
            'is_balanced' => abs($totalDebits - $totalCredits) < 0.01,
        ];
    }

    protected function findAccountByCode(string $code): ?int
    {
        $account = ChartOfAccount::where('code', $code)->first();

        return $account?->id;
    }

    protected function findAccountByType(string $type): ?int
    {
        $account = ChartOfAccount::where('type', $type)->first();

        return $account?->id;
    }

    protected function findExpenseAccountForCategory(?int $categoryId): ?int
    {
        if (! $categoryId) {
            return $this->findAccountByType('expense');
        }

        $category = ExpenseCategory::find($categoryId);
        if ($category && $category->account_id) {
            return $category->account_id;
        }

        return $this->findAccountByType('expense');
    }

    protected function existingPosting(string $postingKey): ?JournalEntry
    {
        return JournalEntry::query()->where('posting_key', $postingKey)->first();
    }

    /** @param array<string, int|null> $accounts */
    protected function requireAccounts(array $accounts): void
    {
        $missing = array_keys(array_filter($accounts, static fn (?int $id): bool => $id === null));

        if ($missing !== []) {
            throw new RuntimeException('Konfigurasi chart of accounts belum lengkap: '.implode(', ', $missing).'.');
        }
    }
}

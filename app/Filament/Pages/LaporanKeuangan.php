<?php

namespace App\Filament\Pages;

use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Payment;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class LaporanKeuangan extends Page
{
    protected string $view = 'filament.pages.laporan-keuangan';

    protected static \UnitEnum|string|null $navigationGroup = 'Reports';
    protected static ?string $navigationLabel = 'Keuangan';
    protected static ?int $navigationSort = 2;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $title = 'Laporan Keuangan';

    public string $dateFrom;
    public string $dateTo;

    public function mount(): void
    {
        $this->dateFrom = now()->startOfYear()->toDateString();
        $this->dateTo = now()->toDateString();
    }

    public function getProfitLoss(): array
    {
        $income = (float) Payment::query()
            ->where('status', 'verified')
            ->whereBetween('payment_date', [$this->dateFrom, $this->dateTo])
            ->sum('amount');

        $expenses = (float) Expense::query()
            ->whereBetween('expense_date', [$this->dateFrom, $this->dateTo])
            ->sum('amount');

        $pendingPayments = (float) Payment::query()
            ->where('status', 'pending')
            ->whereBetween('payment_date', [$this->dateFrom, $this->dateTo])
            ->sum('amount');

        $outstandingInvoices = (float) Invoice::query()
            ->whereIn('status', ['issued', 'partially_paid', 'overdue'])
            ->sum('balance_due');

        return [
            'income' => $income,
            'expenses' => $expenses,
            'net_profit' => $income - $expenses,
            'margin_pct' => $income > 0 ? round(($income - $expenses) / $income * 100, 1) : 0,
            'pending_payments' => $pendingPayments,
            'outstanding_invoices' => $outstandingInvoices,
        ];
    }

    public function getMonthlyPnL(): array
    {
        $revenueData = Payment::query()
            ->where('status', 'verified')
            ->whereBetween('payment_date', [$this->dateFrom, $this->dateTo])
            ->select(DB::raw("DATE_FORMAT(payment_date, '%Y-%m') as month"), DB::raw('SUM(amount) as total'))
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->toArray();

        $expenseData = Expense::query()
            ->whereBetween('expense_date', [$this->dateFrom, $this->dateTo])
            ->select(DB::raw("DATE_FORMAT(expense_date, '%Y-%m') as month"), DB::raw('SUM(amount) as total'))
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->toArray();

        $months = array_unique(array_merge(array_keys($revenueData), array_keys($expenseData)));
        sort($months);

        $labels = array_map(fn ($m) => \Carbon\Carbon::parse($m . '-01')->translatedFormat('M Y'), $months);
        $revenue = array_map(fn ($m) => (float) ($revenueData[$m] ?? 0), $months);
        $expenses = array_map(fn ($m) => (float) ($expenseData[$m] ?? 0), $months);
        $profit = array_map(fn ($i) => $revenue[$i] - $expenses[$i], array_keys($months));

        return compact('labels', 'revenue', 'expenses', 'profit');
    }

    public function getRecentPayments(): array
    {
        return Payment::with(['customer', 'paymentMethod'])
            ->whereBetween('payment_date', [$this->dateFrom, $this->dateTo])
            ->latest('payment_date')
            ->limit(15)
            ->get()
            ->toArray();
    }

    public function getOutstandingInvoices(): array
    {
        return Invoice::with(['customer', 'rentalOrder'])
            ->whereIn('status', ['issued', 'partially_paid', 'overdue'])
            ->where('balance_due', '>', 0)
            ->orderBy('due_date')
            ->limit(15)
            ->get()
            ->toArray();
    }

    public function getExpenseByCategory(): array
    {
        return DB::table('expense_categories')
            ->join('expenses', 'expense_categories.id', '=', 'expenses.expense_category_id')
            ->whereBetween('expenses.expense_date', [$this->dateFrom, $this->dateTo])
            ->select('expense_categories.name', DB::raw('SUM(expenses.amount) as total'))
            ->groupBy('expense_categories.id', 'expense_categories.name')
            ->orderByDesc('total')
            ->get()
            ->toArray();
    }

    public function exportExcel()
    {
        $rows = collect($this->getRecentPayments())->map(fn (array $payment) => [$payment['payment_number'], $payment['customer']['name'] ?? '-', $payment['payment_date'], $payment['payment_method']['name'] ?? '-', $payment['status'], (float) $payment['amount']]);
        return app(\App\Services\ReportExcelService::class)->download('laporan-keuangan-'.$this->dateFrom.'-'.$this->dateTo, ['No. Pembayaran','Customer','Tanggal','Metode','Status','Jumlah'], $rows);
    }
}

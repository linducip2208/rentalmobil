<?php

namespace App\Services;

use App\Models\ChartOfAccount;
use Illuminate\Database\Eloquent\Builder;

class FinancialStatementService
{
    public function trialBalance(string $from, string $to, ?int $locationId = null): array
    {
        $accounts = $this->balances($from, $to, $locationId);
        $debit = round($accounts->sum('debit_balance'), 2);
        $credit = round($accounts->sum('credit_balance'), 2);

        return ['accounts' => $accounts->values(), 'total_debit' => $debit, 'total_credit' => $credit, 'is_balanced' => abs($debit - $credit) < .01];
    }

    public function profitAndLoss(string $from, string $to, ?int $locationId = null): array
    {
        $accounts = $this->balances($from, $to, $locationId);
        $revenue = round($accounts->where('type', 'revenue')->sum('balance'), 2);
        $expenses = round($accounts->where('type', 'expense')->sum('balance'), 2);

        return ['revenue' => $revenue, 'expenses' => $expenses, 'net_profit' => round($revenue - $expenses, 2), 'accounts' => $accounts->whereIn('type', ['revenue', 'expense'])->values()];
    }

    public function balanceSheet(string $asOf, ?int $locationId = null): array
    {
        $accounts = $this->balances('1900-01-01', $asOf, $locationId);
        $assets = round($accounts->where('type', 'asset')->sum('balance'), 2);
        $liabilities = round($accounts->where('type', 'liability')->sum('balance'), 2);
        $equity = round($accounts->where('type', 'equity')->sum('balance'), 2);
        $retained = round($accounts->where('type', 'revenue')->sum('balance') - $accounts->where('type', 'expense')->sum('balance'), 2);

        return ['assets' => $assets, 'liabilities' => $liabilities, 'equity' => $equity, 'retained_earnings' => $retained, 'is_balanced' => abs($assets - ($liabilities + $equity + $retained)) < .01, 'accounts' => $accounts->whereIn('type', ['asset', 'liability', 'equity'])->values()];
    }

    private function balances(string $from, string $to, ?int $locationId)
    {
        $scope = fn (Builder $query) => $query->whereHas('journalEntry', fn (Builder $entry) => $entry->where('status', 'posted')->whereBetween('date', [$from, $to])->when($locationId, fn (Builder $branch) => $branch->where('location_id', $locationId)));

        return ChartOfAccount::query()->withSum(['journalLines as period_debit' => $scope], 'debit')->withSum(['journalLines as period_credit' => $scope], 'credit')->orderBy('code')->get()->map(function ($account) {
            $debit = (float) $account->period_debit;
            $credit = (float) $account->period_credit;
            $balance = $account->normal_balance === 'credit' ? $credit - $debit : $debit - $credit;

            return ['id' => $account->id, 'code' => $account->code, 'name' => $account->name, 'type' => $account->type, 'debit_balance' => max($debit - $credit, 0), 'credit_balance' => max($credit - $debit, 0), 'balance' => round($balance, 2)];
        });
    }
}

<?php

namespace App\Services;

use App\Models\CashFlowSnapshot;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Subscription;
use Illuminate\Support\Facades\DB;

/**
 * Proyeksi arus kas 90 hari:
 * - Inflow: invoice jatuh tempo + tagihan langganan + rata-rata pembayaran walk-in.
 * - Outflow: rata-rata expense bulanan (prorata harian) + jadwal servis + PO draft.
 */
class CashFlowProjectionService
{
    public function project(int $horizonDays = 90): CashFlowSnapshot
    {
        $today = now()->startOfDay();
        $end = $today->copy()->addDays($horizonDays);

        $invoiceInflow = (float) Invoice::whereIn('status', ['issued', 'partially_paid', 'overdue'])
            ->whereBetween('due_date', [$today, $end])
            ->sum('balance_due');

        $subscriptionInflow = (float) Subscription::where('status', 'active')
            ->whereBetween('current_period_end', [$today, $end])
            ->sum('price_per_cycle');

        $avgDailyWalkIn = $this->avgDailyPayments(30);
        $inflow = round($invoiceInflow + $subscriptionInflow + ($avgDailyWalkIn * $horizonDays), 2);

        $avgDailyExpense = (float) Expense::whereBetween('expense_date', [now()->subDays(60), now()])
            ->sum('amount') / 60;

        $maintenanceOutflow = $this->scheduledMaintenanceCost($end);
        $poOutflow = (float) \App\Models\SparePartPurchaseOrder::whereIn('status', ['draft', 'sent'])
            ->whereBetween('expected_at', [$today, $end->copy()->addDays(7)])
            ->sum('total_amount');

        $outflow = round(($avgDailyExpense * $horizonDays) + $maintenanceOutflow + $poOutflow, 2);

        return CashFlowSnapshot::create([
            'as_of_date' => $today->toDateString(),
            'horizon_days' => $horizonDays,
            'projected_inflow' => $inflow,
            'projected_outflow' => $outflow,
            'net_projection' => round($inflow - $outflow, 2),
            'breakdown' => [
                'inflow' => [
                    'invoices_due' => $invoiceInflow,
                    'subscriptions' => $subscriptionInflow,
                    'walk_in_avg_daily' => round($avgDailyWalkIn, 2),
                    'walk_in_total' => round($avgDailyWalkIn * $horizonDays, 2),
                ],
                'outflow' => [
                    'expenses_avg_60d' => round($avgDailyExpense * $horizonDays, 2),
                    'scheduled_maintenance' => $maintenanceOutflow,
                    'purchase_orders_draft_sent' => $poOutflow,
                ],
            ],
        ]);
    }

    public function weeklySeries(CashFlowSnapshot $snapshot): array
    {
        // Distribusi linear inflow/outflow per minggu untuk chart sederhana.
        $weeks = max(1, intdiv($snapshot->horizon_days, 7));
        $weeklyIn = round($snapshot->projected_inflow / $weeks, 2);
        $weeklyOut = round($snapshot->projected_outflow / $weeks, 2);

        $labels = [];
        $net = [];
        $cumulative = [];
        $running = 0.0;

        for ($i = 0; $i < $weeks; $i++) {
            $labels[] = 'M+' . ($i + 1);
            $running += $weeklyIn - $weeklyOut;
            $net[] = round($weeklyIn - $weeklyOut, 2);
            $cumulative[] = round($running, 2);
        }

        return compact('labels', 'net', 'cumulative');
    }

    protected function avgDailyPayments(int $days): float
    {
        return (float) DB::table('payments')
            ->where('status', 'verified')
            ->whereBetween('payment_date', [now()->subDays($days), now()])
            ->sum('amount') / max(1, $days);
    }

    protected function scheduledMaintenanceCost($until): float
    {
        return (float) DB::table('service_schedules')
            ->whereIn('status', ['pending', 'scheduled'])
            ->whereBetween('scheduled_date', [now(), $until])
            ->sum('estimated_cost');
    }
}

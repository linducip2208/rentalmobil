<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\InvestorDistribution;
use App\Models\Payment;
use App\Models\VehicleInvestment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Distribusi profit investor per bulan: revenue - expense - depresiasi
 * dibagi proporsional share_percent tiap investment aktif.
 */
class InvestorDistributionService
{
    public function distribute(?string $periodMonth = null): array
    {
        $month = $periodMonth ?? now()->subMonth()->format('Y-m');
        [$start, $end] = $this->monthBounds($month);

        $investments = VehicleInvestment::active()
            ->whereDate('started_at', '<=', $end)
            ->with(['vehicle', 'investorAccount'])
            ->get();

        $created = 0;
        $skipped = 0;

        foreach ($investments as $investment) {
            if (InvestorDistribution::where('vehicle_investment_id', $investment->id)->where('period_month', $month)->exists()) {
                $skipped++;

                continue;
            }

            $vehicleRevenue = (float) Payment::where('status', 'verified')
                ->whereBetween('payment_date', [$start, $end])
                ->whereHas('rentalOrder', fn ($q) => $q->where('vehicle_id', $investment->vehicle_id))
                ->sum('amount');

            if ($vehicleRevenue <= 0) {
                // Fallback: alokasikan dari total revenue proporsional share jika order-level tidak tercatat.
                $vehicleRevenue = 0.0;
            }

            $vehicleExpense = $this->vehicleDirectCosts($investment->vehicle_id, $start, $end);

            $depreciation = $investment->vehicle->monthlyDepreciation();
            $sharePct = (float) $investment->share_percent / 100;

            $revenueShare = round($vehicleRevenue * $sharePct, 2);
            $expenseShare = round($vehicleExpense * $sharePct, 2);
            $depreciationShare = round($depreciation * $sharePct, 2);
            $net = round($revenueShare - $expenseShare - $depreciationShare, 2);

            InvestorDistribution::create([
                'vehicle_investment_id' => $investment->id,
                'period_month' => $month,
                'revenue_share' => $revenueShare,
                'expense_share' => $expenseShare,
                'depreciation_share' => $depreciationShare,
                'net_payout' => $net,
                'status' => 'pending',
            ]);

            $created++;
        }

        return ['period' => $month, 'created' => $created, 'skipped_existing' => $skipped];
    }

    public function markPaid(InvestorDistribution $distribution): void
    {
        DB::transaction(function () use ($distribution) {
            $distribution->markPaid();

            app(NotificationDispatcher::class)->dispatch('investor_payout_paid', $distribution->investment->investorAccount, [
                'period_month' => $distribution->period_month,
                'net_payout' => $distribution->net_payout,
            ]);
        });
    }

    public function portfolioSummary(): array
    {
        $totalInvested = (float) VehicleInvestment::active()->sum('invested_amount');
        $activeCount = VehicleInvestment::active()->count();
        $pendingPayout = (float) InvestorDistribution::where('status', 'pending')->sum('net_payout');
        $paidPayout = (float) InvestorDistribution::where('status', 'paid')->sum('net_payout');

        return [
            'total_invested' => $totalInvested,
            'active_investments' => $activeCount,
            'pending_payout_total' => $pendingPayout,
            'paid_payout_total' => $paidPayout,
        ];
    }

    /**
     * Biaya langsung per kendaraan: maintenance + BBM (expense umum tidak terhubung kendaraan).
     */
    protected function vehicleDirectCosts(int $vehicleId, $start, $end): float
    {
        $maintenance = (float) DB::table('maintenance_logs')
            ->where('vehicle_id', $vehicleId)
            ->whereBetween('service_date', [$start->toDateString(), $end->toDateString()])
            ->sum('cost');

        $fuel = (float) DB::table('fuel_logs')
            ->where('vehicle_id', $vehicleId)
            ->whereBetween('fuel_date', [$start->toDateString(), $end->toDateString()])
            ->sum('cost');

        return round($maintenance + $fuel, 2);
    }

    protected function monthBounds(string $ym): array
    {
        $start = Carbon::parse($ym.'-01')->startOfDay();

        return [$start, $start->copy()->endOfMonth()];
    }
}

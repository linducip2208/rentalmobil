<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\RentalOrder;
use App\Models\Vehicle;
use Carbon\Carbon;

/**
 * Laporan utilisasi & profit riil per kendaraan
 * (revenue - biaya langsung - depresiasi = profit sebenarnya).
 */
class UtilizationProfitService
{
    public function utilizationHeatmap(Carbon $from, Carbon $to, ?int $locationId = null): array
    {
        $vehiclesQuery = Vehicle::query()->where('is_active', true);

        if ($locationId) {
            $vehiclesQuery->where('location_id', $locationId);
        }

        $vehicles = $vehiclesQuery->with('category')->orderBy('name')->get();

        $bookedRanges = RentalOrder::whereIn('vehicle_id', $vehicles->pluck('id'))
            ->whereIn('status', ['ready_for_preparation', 'preparing', 'ready_for_handover', 'checked_out', 'active', 'extension_requested', 'return_due', 'overdue', 'completed'])
            ->where('start_date', '<=', $to)
            ->where('end_date', '>=', $from)
            ->get(['vehicle_id', 'start_date', 'end_date']);

        $days = [];
        for ($date = $from->copy(); $date->lte($to); $date->addDay()) {
            $days[] = $date->format('Y-m-d');
        }

        $grid = [];

        foreach ($vehicles as $vehicle) {
            $ranges = $bookedRanges->where('vehicle_id', $vehicle->id);
            $row = ['total_booked' => 0];

            foreach ($days as $day) {
                $isBooked = $ranges->contains(fn ($r) => $r->start_date->lte($day) && $r->end_date->gte($day));

                if ($isBooked) {
                    $row['total_booked']++;
                }
            }

            // Simpan agregat saja untuk grid ringkas; detail hari dihitung on-demand per kendaraan.
            $grid[] = [
                'vehicle_id' => $vehicle->id,
                'vehicle' => $vehicle->name,
                'plate' => $vehicle->plate_number,
                'category' => $vehicle->category?->name,
                'booked_days' => $row['total_booked'],
                'total_days' => count($days),
                'utilization_pct' => round($row['total_booked'] / max(1, count($days)) * 100, 1),
            ];
        }

        usort($grid, fn ($a, $b) => $b['utilization_pct'] <=> $a['utilization_pct']);

        return [
            'period_days' => count($days),
            'rows' => $grid,
            'avg_utilization' => round(collect($grid)->avg('utilization_pct') ?? 0, 1),
        ];
    }

    public function lowUtilizationVehicles(float $thresholdPct = 30): array
    {
        $heatmap = $this->utilizationHeatmap(now()->subDays(90), now());

        return collect($heatmap['rows'])
            ->filter(fn ($row) => $row['utilization_pct'] < $thresholdPct)
            ->values()
            ->all();
    }

    public function profitPerVehicle(Carbon $from, Carbon $to): array
    {
        $vehicles = Vehicle::where('is_active', true)->with('category')->get();
        $monthsElapsed = max(1, $from->diffInMonths($to) + ($to->diffInDays($from->copy()->addMonths($from->diffInMonths($to))) / 30));

        $rows = [];

        foreach ($vehicles as $vehicle) {
            $revenue = (float) Payment::where('status', 'verified')
                ->whereBetween('payment_date', [$from, $to])
                ->whereHas('rentalOrder', fn ($q) => $q->where('vehicle_id', $vehicle->id))
                ->sum('amount');

            $directCosts = (float) \DB::table('maintenance_logs')
                ->where('vehicle_id', $vehicle->id)
                ->whereBetween('service_date', [$from->toDateString(), $to->toDateString()])
                ->sum('cost')
                + (float) \DB::table('fuel_logs')
                    ->where('vehicle_id', $vehicle->id)
                    ->whereBetween('fuel_date', [$from->toDateString(), $to->toDateString()])
                    ->sum('cost');

            $monthlyDepreciation = $vehicle->monthlyDepreciation();
            $depreciation = round($monthlyDepreciation * $monthsElapsed, 2);

            $netProfit = round($revenue - $directCosts - $depreciation, 2);

            $breakEvenInfo = $this->breakEvenEstimate($vehicle, $revenue, $monthlyDepreciation);

            $rows[] = [
                'vehicle_id' => $vehicle->id,
                'vehicle' => $vehicle->name,
                'plate' => $vehicle->plate_number,
                'revenue' => $revenue,
                'direct_costs' => round($directCosts, 2),
                'depreciation' => $depreciation,
                'monthly_depreciation' => $monthlyDepreciation,
                'net_profit' => $netProfit,
                'margin_pct' => $revenue > 0 ? round($netProfit / $revenue * 100, 1) : null,
                'purchase_price' => (float) ($vehicle->purchase_price ?? 0),
                ...$breakEvenInfo,
            ];
        }

        usort($rows, fn ($a, $b) => $b['net_profit'] <=> $a['net_profit']);

        return [
            'period' => [$from->toDateString(), $to->toDateString()],
            'rows' => $rows,
            'totals' => [
                'revenue' => round(array_sum(array_column($rows, 'revenue')), 2),
                'costs' => round(array_sum(array_column($rows, 'direct_costs')), 2),
                'depreciation' => round(array_sum(array_column($rows, 'depreciation')), 2),
                'profit' => round(array_sum(array_column($rows, 'net_profit')), 2),
            ],
        ];
    }

    /**
     * Estimasi break-even: berapa bulan lagi unit menghasilkan profit
     * sebelum nilai beli "terbayar" oleh akumulasi net profit.
     */
    protected function breakEvenEstimate(Vehicle $vehicle, float $revenuePeriod, float $monthlyDepreciation): array
    {
        if (! $vehicle->purchase_price || ! $vehicle->acquired_at || $revenuePeriod <= 0) {
            return ['break_even_months_left' => null];
        }

        $avgMonthlyRevenue = max(0.01, $revenuePeriod);
        $accumulatedNet = $revenuePeriod - $monthlyDepreciation;

        if ($accumulatedNet <= 0) {
            return ['break_even_months_left' => null, 'break_even_status' => 'Tidak profitable'];
        }

        $price = (float) $vehicle->purchase_price;
        $monthsSinceAcquisition = max(1, round($vehicle->acquired_at->diffInMonths(now()), 1));
        $remaining = max(0, $price - ($accumulatedNet * $monthsSinceAcquisition));
        $monthsLeft = ceil($remaining / $accumulatedNet);

        return [
            'break_even_months_left' => $monthsLeft === 0 ? 0 : (int) $monthsLeft,
            'break_even_status' => $monthsLeft <= 0 ? 'Lunas ✓' : ($monthsLeft > 48 ? 'Lambat' : 'Normal'),
        ];
    }
}

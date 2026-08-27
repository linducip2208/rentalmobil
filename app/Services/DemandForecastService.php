<?php

namespace App\Services;

use App\Models\Category;
use App\Models\DemandForecast;
use App\Models\Location;
use App\Models\RentalOrder;
use App\Models\SeasonPeriod;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * Demand forecasting sederhana namun deterministik:
 * baseline occupancy historis 90 hari per kategori+lokasi,
 * dikali faktor weekday/weekend dan multiplier musim (SeasonPeriod).
 */
class DemandForecastService
{
    public const HORIZON_DAYS = 30;

    public function generate(): array
    {
        $results = ['created' => 0, 'updated' => 0];

        $scopes = [];
        foreach (Category::all() as $category) {
            foreach (Location::active()->get() as $location) {
                if (Vehicle::where('category_id', $category->id)->where('location_id', $location->id)->where('is_active', true)->exists()) {
                    $scopes[] = [$category->id, $location->id];
                }
            }
        }

        foreach ($scopes as [$categoryId, $locationId]) {
            $baseline = $this->historicalOccupancy($categoryId, $locationId);

            foreach (now()->startOfDay()->daysUntil(now()->copy()->addDays(self::HORIZON_DAYS)) as $date) {
                /** @var Carbon $date */
                $weekendFactor = in_array($date->dayOfWeekIso, [6, 7]) ? 1.25 : ($date->dayOfWeekIso === 5 ? 1.15 : 1.0);

                $season = SeasonPeriod::forDate($date->toDateString(), $locationId);
                $seasonFactor = $season ? min(2.0, (float) $season->multiplier) : 1.0;

                $occupancy = min(1.0, round($baseline * $weekendFactor * $seasonFactor, 4));
                $confidence = $this->confidenceFor($date);

                $model = DemandForecast::updateOrCreate(
                    [
                        'forecast_date' => $date->toDateString(),
                        'category_id' => $categoryId,
                        'location_id' => $locationId,
                    ],
                    [
                        'predicted_occupancy' => $occupancy,
                        'confidence' => $confidence,
                        'factors' => [
                            'baseline_90d' => $baseline,
                            'weekend_factor' => $weekendFactor,
                            'season_factor' => $seasonFactor,
                            'season_name' => $season?->name,
                        ],
                    ]
                );

                $model->wasRecentlyCreated ? $results['created']++ : $results['updated']++;
            }
        }

        Cache::forget('demand_forecast_multiplier');

        return $results;
    }

    /**
     * Multiplier harga berbasis forecast untuk tanggal sewa tertentu.
     * Ambang & multiplier diatur pemilik via SystemSetting "demand_forecast_pricing" (JSON):
     * {"enabled": true, "high_threshold": 0.8, "high_multiplier": 1.2, "low_threshold": 0.3, "low_multiplier": 0.92}
     */
    public function priceMultiplier(Vehicle $vehicle, string $startDate): array
    {
        $config = \App\Models\SystemSetting::get('demand_forecast_pricing');
        $config = is_string($config) ? json_decode($config, true) : $config;

        if (!is_array($config) || !filter_var($config['enabled'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            return ['multiplier' => 1.0, 'occupancy' => null];
        }

        $forecast = DemandForecast::forDate(Carbon::parse($startDate)->toDateString())
            ->where(function ($q) use ($vehicle) {
                $q->where(function ($qq) use ($vehicle) {
                    $qq->whereNull('category_id')->orWhere('category_id', $vehicle->category_id);
                })->where(function ($qq) use ($vehicle) {
                    $qq->whereNull('location_id')->orWhere('location_id', $vehicle->location_id);
                });
            })
            ->orderByRaw('category_id IS NULL, location_id IS NULL')
            ->first();

        if (!$forecast) {
            return ['multiplier' => 1.0, 'occupancy' => null];
        }

        $occupancy = (float) $forecast->predicted_occupancy;
        $multiplier = match (true) {
            $occupancy >= (float) ($config['high_threshold'] ?? 0.8) => (float) ($config['high_multiplier'] ?? 1.2),
            $occupancy <= (float) ($config['low_threshold'] ?? 0.3) => (float) ($config['low_multiplier'] ?? 0.92),
            default => 1.0,
        };

        return ['multiplier' => $multiplier, 'occupancy' => $occupancy];
    }

    protected function historicalOccupancy(int $categoryId, int $locationId): float
    {
        $from = now()->subDays(90);
        $vehicleIds = Vehicle::where('category_id', $categoryId)
            ->where('location_id', $locationId)
            ->pluck('id');

        if ($vehicleIds->isEmpty()) {
            return 0.5;
        }

        $bookedDays = (int) RentalOrder::whereIn('vehicle_id', $vehicleIds)
            ->whereIn('status', ['ready_for_preparation', 'preparing', 'ready_for_handover', 'checked_out', 'active', 'extension_requested', 'return_due', 'overdue', 'completed'])
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', $from)
            ->selectRaw('SUM(DATEDIFF(LEAST(end_date, ?), GREATEST(start_date, ?)) + 1) as days', [now()->toDateString(), $from->toDateString()])
            ->value('days');

        $totalVehicleDays = max(1, $vehicleIds->count() * 90);

        return min(1.0, max(0.05, $bookedDays / $totalVehicleDays));
    }

    protected function confidenceFor(Carbon $date): float
    {
        $daysAhead = now()->diffInDays($date);

        // Semakin jauh horizon, semakin rendah keyakinan.
        return round(max(40, 95 - $daysAhead), 2);
    }
}

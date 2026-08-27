<?php

namespace App\Services;

use App\Models\Delivery;
use App\Models\DispatchRecommendation;
use App\Models\Driver;
use App\Models\DriverBehaviorEvent;
use App\Models\DriverRating;
use App\Models\GpsTracker;
use App\Models\RentalOrder;
use App\Models\Vehicle;

/**
 * Smart dispatch: rekomendasi driver + unit untuk delivery berdasarkan
 * rating, perilaku berkendara, kedekatan posisi GPS, dan status armada.
 */
class SmartDispatchService
{
    public function recommendForPendingDeliveries(int $limit = 20): array
    {
        $pending = Delivery::whereIn('status', ['scheduled', 'pending'])
            ->whereDoesntHave('dispatchRecommendations', fn ($q) => $q->whereIn('status', ['suggested', 'accepted']))
            ->with(['toLocation'])
            ->orderBy('scheduled_date')
            ->limit($limit)
            ->get();

        $created = 0;

        foreach ($pending as $delivery) {
            $recommendation = $this->recommend($delivery);

            if ($recommendation) {
                $created++;
            }
        }

        return ['deliveries_checked' => $pending->count(), 'recommendations_created' => $created];
    }

    public function recommend(Delivery $delivery): ?DispatchRecommendation
    {
        DispatchRecommendation::where('delivery_id', $delivery->id)->whereIn('status', ['suggested'])->update(['status' => 'dismissed']);

        [$bestDriver, $driverScore, $driverReasons] = $this->scoreDrivers($delivery);
        [$bestVehicle, $vehicleScore, $vehicleReasons] = $this->scoreVehicles($delivery);

        if (! $bestDriver && ! $bestVehicle) {
            return null;
        }

        $combinedScore = round(($driverScore + $vehicleScore) / 2, 2);

        return DispatchRecommendation::create([
            'delivery_id' => $delivery->id,
            'recommended_driver_id' => $bestDriver?->id,
            'recommended_vehicle_id' => $delivery->vehicle_id ?? $bestVehicle?->id,
            'score' => $combinedScore,
            'reasons' => [
                'driver' => $driverReasons,
                'vehicle' => $vehicleReasons,
            ],
        ]);
    }

    /**
     * @return array{0: ?Driver, 1: float, 2: array}
     */
    protected function scoreDrivers(Delivery $delivery): array
    {
        $drivers = Driver::where('is_active', true)->get();

        if ($drivers->isEmpty()) {
            return [null, 0.0, ['Tidak ada driver aktif']];
        }

        $destinationLat = $delivery->toLocation?->latitude;
        $destinationLng = $delivery->toLocation?->longitude;

        $scored = $drivers->map(function (Driver $driver) use ($destinationLat, $destinationLng) {
            $reasons = [];
            $score = 50.0;

            // Rating pelanggan (0-5 → 0-30 poin).
            $avgRating = (float) DriverRating::where('driver_id', $driver->id)->avg('rating');

            if ($avgRating > 0) {
                $score += ($avgRating / 5) * 30;
                $reasons[] = 'Rating '.round($avgRating, 2).'/5';
            }

            // Penalti insiden perilaku 90 hari.
            $incidents = (int) DriverBehaviorEvent::where('driver_id', $driver->id)
                ->where('occurred_at', '>=', now()->subDays(90))
                ->count();

            if ($incidents > 0) {
                $penalty = min(25, $incidents * 2);
                $score -= $penalty;
                $reasons[] = "{$incidents} insiden berkendara (90 hari)";
            } else {
                $score += 10;
                $reasons[] = 'Tanpa insiden 90 hari';
            }

            // Bonus kedekatan GPS ke lokasi tujuan (via unit yang sedang digunakan driver).
            if ($destinationLat && $destinationLng) {
                $activeVehicleId = RentalOrder::where('driver_id', $driver->id)
                    ->whereIn('status', ['checked_out', 'active'])
                    ->value('vehicle_id');

                $tracker = GpsTracker::where('vehicle_id', $activeVehicleId)
                    ->whereNotNull('last_latitude')
                    ->first();

                if ($tracker) {
                    $distanceKm = $this->haversineKm(
                        (float) $tracker->last_latitude,
                        (float) $tracker->last_longitude,
                        (float) $destinationLat,
                        (float) $destinationLng
                    );

                    $proximityBonus = max(0, 20 - min(20, $distanceKm));
                    $score += $proximityBonus;

                    if ($distanceKm < 5) {
                        $reasons[] = "Dekat tujuan ({$distanceKm} km)";
                    }
                }
            }

            // Beban kerja: lebih sedikit order aktif → bonus.
            $activeOrders = RentalOrder::where('driver_id', $driver->id)->whereIn('status', ['checked_out', 'active'])->count();
            $workloadPenalty = min(15, $activeOrders * 5);
            $score -= $workloadPenalty;

            if ($activeOrders === 0) {
                $score += 8;
                $reasons[] = 'Bebas jadwal (tidak ada order aktif)';
            }

            return [$driver, max(0, min(100, round($score, 2))), $reasons];
        });

        $best = $scored->sortByDesc(fn ($row) => $row[1])->first();

        return [$best[0], $best[1], $best[2]];
    }

    protected function scoreVehicles(Delivery $delivery): array
    {
        if ($delivery->vehicle_id) {
            $vehicle = Vehicle::find($delivery->vehicle_id);

            return [$vehicle, 80.0, ['Unit sudah ditentukan']];
        }

        $vehicles = Vehicle::available()->limit(20)->get();

        if ($vehicles->isEmpty()) {
            return [null, 0.0, ['Tidak ada unit tersedia']];
        }

        $best = null;
        $bestScore = -1;
        $bestReasons = [];

        foreach ($vehicles as $vehicle) {
            $score = 60.0;
            $reasons = ['Status tersedia'];

            // Prioritaskan unit dengan dokumen valid paling lama.
            $daysToStnk = $vehicle->stnk_due_date ? now()->diffInDays($vehicle->stnk_due_date, false) : 999;

            if ($daysToStnk < 14) {
                $score -= 15;
                $reasons[] = 'STNK dekat jatuh tempo';
            } else {
                $score += 10;
                $reasons[] = 'Dokumen aman';
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $vehicle;
                $bestReasons = $reasons;
            }
        }

        return [$best, $bestScore, $bestReasons];
    }

    public function accept(DispatchRecommendation $recommendation): void
    {
        $recommendation->update(['status' => 'accepted']);

        $delivery = $recommendation->delivery;

        if ($recommendation->recommended_driver_id) {
            $delivery->update(['driver_id' => $recommendation->recommended_driver_id]);
        }

        if ($recommendation->recommended_vehicle_id && ! $delivery->vehicle_id) {
            $delivery->update(['vehicle_id' => $recommendation->recommended_vehicle_id]);
        }
    }

    protected function haversineKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;

        return round(6371.0 * 2 * asin(min(1.0, sqrt($a))), 2);
    }
}

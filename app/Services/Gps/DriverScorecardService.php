<?php

namespace App\Services\Gps;

use App\Models\Driver;
use App\Models\DriverBehaviorEvent;
use App\Models\DriverRating;
use App\Models\DriverScorecard;
use App\Models\RentalOrder;

/**
 * Scorecard driver: agregasi perilaku berkendara + rating pelanggan
 * menjadi skor 0-100 dan ranking per periode.
 */
class DriverScorecardService
{
    public function generate(string $periodStart, string $periodEnd): array
    {
        $drivers = Driver::where('is_active', true)->get();
        $created = 0;
        $updated = 0;

        foreach ($drivers as $driver) {
            $events = DriverBehaviorEvent::where('driver_id', $driver->id)
                ->whereBetween('occurred_at', [$periodStart.' 00:00:00', $periodEnd.' 23:59:59'])
                ->selectRaw('type, COUNT(*) as total')
                ->groupBy('type')
                ->pluck('total', 'type');

            $trips = RentalOrder::where('driver_id', $driver->id)
                ->whereIn('status', ['completed'])
                ->whereBetween('end_date', [$periodStart, $periodEnd])
                ->count();

            $avgRating = (float) DriverRating::where('driver_id', $driver->id)
                ->whereBetween('created_at', [$periodStart.' 00:00:00', $periodEnd.' 23:59:59'])
                ->avg('rating');

            $score = $this->calculateScore(
                (int) ($events['overspeed'] ?? 0),
                (int) ($events['harsh_brake'] ?? 0),
                (int) ($events['harsh_acceleration'] ?? 0),
                (int) ($events['long_idle'] ?? 0),
                (int) ($events['geofence_violation'] ?? 0),
                $trips,
                $avgRating ?: null
            );

            $card = DriverScorecard::updateOrCreate(
                ['driver_id' => $driver->id, 'period_start' => $periodStart, 'period_end' => $periodEnd],
                [
                    'overspeed_count' => $events['overspeed'] ?? 0,
                    'harsh_brake_count' => $events['harsh_brake'] ?? 0,
                    'harsh_acceleration_count' => $events['harsh_acceleration'] ?? 0,
                    'long_idle_count' => $events['long_idle'] ?? 0,
                    'geofence_violation_count' => $events['geofence_violation'] ?? 0,
                    'trips' => $trips,
                    'avg_rating' => round($avgRating, 2),
                    'score' => $score,
                ]
            );

            $card->wasRecentlyCreated ? $created++ : $updated++;
        }

        $this->assignRanks($periodStart, $periodEnd);

        return ['drivers' => $drivers->count(), 'created' => $created, 'updated' => $updated];
    }

    protected function calculateScore(int $overspeed, int $harshBrake, int $harshAccel, int $longIdle, int $geofence, int $trips, ?float $rating): float
    {
        // Bobot penalti per insiden.
        $penalty = $overspeed * 3 + $harshBrake * 2 + $harshAccel * 2 + $longIdle * 1 + $geofence * 4;

        // Normalisasi terhadap jumlah trip supaya driver sibuk tidak dihukum berlebihan.
        if ($trips > 0) {
            $penalty = min(60, $penalty / max(1.0, sqrt($trips)));
        } else {
            $penalty = min(60, $penalty);
        }

        $base = max(40, 100 - $penalty);

        // Bonus rating pelanggan (1-5 → -10..+10).
        if ($rating !== null && $rating > 0) {
            $base += ($rating - 3.5) * 6.67;
        }

        return round(max(0, min(100, $base)), 2);
    }

    protected function assignRanks(string $periodStart, string $periodEnd): void
    {
        $cards = DriverScorecard::where('period_start', $periodStart)->where('period_end', $periodEnd)
            ->orderByDesc('score')
            ->orderByDesc('avg_rating')
            ->get();

        foreach ($cards as $index => $card) {
            $card->update(['rank_position' => $index + 1]);
        }
    }
}

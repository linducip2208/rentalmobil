<?php

namespace App\Services\Gps;

use App\Models\GpsLog;
use App\Models\GpsTracker;
use Carbon\CarbonImmutable;

/**
 * Route replay: polyline pergerakan + deteksi stop + titik heat dari gps_logs.
 */
class RouteReplayService
{
    public function replay(GpsTracker $tracker, string $from, string $to): array
    {
        $logs = GpsLog::where('gps_tracker_id', $tracker->id)
            ->whereBetween('logged_at', [CarbonImmutable::parse($from)->startOfDay(), CarbonImmutable::parse($to)->endOfDay()])
            ->orderBy('logged_at')
            ->get(['latitude', 'longitude', 'speed', 'logged_at']);

        if ($logs->isEmpty()) {
            return [
                'polyline' => [],
                'stops' => [],
                'heat_points' => [],
                'distance_km' => 0,
                'max_speed' => 0,
                'avg_speed' => 0,
                'points' => 0,
            ];
        }

        $distanceKm = 0.0;
        $speedSum = 0.0;
        $maxSpeed = 0.0;
        $stops = [];
        $currentStop = null;

        foreach ($logs as $index => $log) {
            $speedSum += (float) $log->speed;
            $maxSpeed = max($maxSpeed, (float) $log->speed);

            if ($index > 0) {
                $prev = $logs[$index - 1];
                $segmentKm = $this->haversineKm((float) $prev->latitude, (float) $prev->longitude, (float) $log->latitude, (float) $log->longitude);
                $distanceKm += $segmentKm;
            }

            // Deteksi stop: speed < 5 km/h selama >= 5 menit
            if ((float) $log->speed < 5) {
                $currentStop ??= ['start' => $log->logged_at, 'lat' => (float) $log->latitude, 'lng' => (float) $log->longitude];

                if ($log->logged_at->diffInMinutes($currentStop['start']) >= 5 && ! isset($currentStop['end'])) {
                    $currentStop['end'] = $log->logged_at;
                    $currentStop['duration_min'] = $currentStop['start']->diffInMinutes($currentStop['end']);
                }
            } elseif ($currentStop !== null) {
                if (isset($currentStop['end'])) {
                    $stops[] = $currentStop + ['address' => 'Lat '.round($currentStop['lat'], 4).', Lng '.round($currentStop['lng'], 4)];
                }
                $currentStop = null;
            }

            if (isset($currentStop['end']) && ($index === $logs->count() - 1)) {
                $stops[] = $currentStop + ['address' => 'Lat '.round($currentStop['lat'], 4).', Lng '.round($currentStop['lng'], 4)];
            }
        }

        if ($currentStop !== null && isset($currentStop['end'])) {
            $stops[] = $currentStop + ['address' => 'Lat '.round($currentStop['lat'], 4).', Lng '.round($currentStop['lng'], 4)];
        }

        // Heat points: sampling maksimal 300 titik untuk performa peta
        $heatPoints = $logs->count() > 300
            ? $logs->filter(fn ($_, $i) => $i % intdiv($logs->count(), 300) === 0)->values()
            : $logs;

        return [
            'polyline' => $heatPoints->map(fn ($l) => [(float) $l->latitude, (float) $l->longitude])->toArray(),
            'stops' => collect($stops)->map(fn ($s) => [
                'lat' => $s['lat'],
                'lng' => $s['lng'],
                'started_at' => $s['start']->format('H:i'),
                'ended_at' => $s['end']->format('H:i'),
                'duration_min' => $s['duration_min'],
                'address' => $s['address'],
            ])->values()->all(),
            'heat_points' => $heatPoints->map(fn ($l) => [
                'lat' => (float) $l->latitude,
                'lng' => (float) $l->longitude,
                'intensity' => min(1.0, (float) $l->speed / 60),
            ])->toArray(),
            'distance_km' => round($distanceKm, 1),
            'max_speed' => round($maxSpeed, 1),
            'avg_speed' => round($speedSum / max(1, $logs->count()), 1),
            'points' => $logs->count(),
        ];
    }

    protected function haversineKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371.0;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;

        return 2 * $earthRadius * asin(min(1.0, sqrt($a)));
    }
}

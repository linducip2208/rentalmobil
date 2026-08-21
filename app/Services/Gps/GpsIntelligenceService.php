<?php

namespace App\Services\Gps;

use App\Models\DriverBehaviorEvent;
use App\Models\GpsGeofence;
use App\Models\GpsLog;
use App\Models\SystemSetting;

class GpsIntelligenceService
{
    public function evaluate(GpsLog $log): void
    {
        $order = $log->vehicle->rentalOrders()->whereIn('status', ['checked_out', 'active', 'overdue'])->latest()->first();
        $driverId = $order?->driver_id;
        $limit = (float) SystemSetting::get('gps_behavior_speed_limit_kmh', 100);
        if ((float) $log->speed > $limit) {
            $this->record($log, $driverId, 'overspeed', min(5, 1 + (int) (((float) $log->speed - $limit) / 20)), ['speed' => (float) $log->speed, 'limit' => $limit]);
        }
        $previous = GpsLog::where('vehicle_id', $log->vehicle_id)->where('id', '!=', $log->id)->latest('recorded_at')->first();
        if ($previous && $previous->recorded_at->diffInSeconds($log->recorded_at) <= 120) {
            $delta = (float) $log->speed - (float) $previous->speed;
            $threshold = (float) SystemSetting::get('gps_behavior_speed_delta_kmh', 30);
            if ($delta > $threshold) $this->record($log, $driverId, 'harsh_acceleration', 2, ['delta' => $delta]);
            if ($delta < -$threshold) $this->record($log, $driverId, 'harsh_brake', 2, ['delta' => $delta]);
        }
        foreach (GpsGeofence::where('is_active', true)->get() as $geofence) {
            $inside = $this->contains($geofence->geometry ?? [], (float) $log->latitude, (float) $log->longitude);
            $violation = $geofence->type === 'restricted' ? $inside : ($geofence->type === 'allowed' && ! $inside);
            if ($violation) $this->record($log, $driverId, 'geofence_violation', 4, ['geofence_id' => $geofence->id, 'geofence' => $geofence->name, 'rule' => $geofence->type]);
        }
    }

    private function contains(array $geometry, float $latitude, float $longitude): bool
    {
        if (isset($geometry['latitude'], $geometry['longitude'], $geometry['radius_meter'])) {
            $earth = 6371000;
            $latDelta = deg2rad($latitude - (float) $geometry['latitude']);
            $lonDelta = deg2rad($longitude - (float) $geometry['longitude']);
            $a = sin($latDelta / 2) ** 2 + cos(deg2rad((float) $geometry['latitude'])) * cos(deg2rad($latitude)) * sin($lonDelta / 2) ** 2;
            return $earth * 2 * atan2(sqrt($a), sqrt(1 - $a)) <= (float) $geometry['radius_meter'];
        }
        $points = $geometry['points'] ?? [];
        if (is_string($points)) $points = json_decode($points, true) ?: [];
        $inside = false;
        for ($i = 0, $j = count($points) - 1; $i < count($points); $j = $i++) {
            $yi = (float) ($points[$i]['latitude'] ?? $points[$i][0] ?? 0);
            $xi = (float) ($points[$i]['longitude'] ?? $points[$i][1] ?? 0);
            $yj = (float) ($points[$j]['latitude'] ?? $points[$j][0] ?? 0);
            $xj = (float) ($points[$j]['longitude'] ?? $points[$j][1] ?? 0);
            if ((($yi > $latitude) !== ($yj > $latitude)) && ($longitude < ($xj - $xi) * ($latitude - $yi) / (($yj - $yi) ?: 0.0000001) + $xi)) $inside = ! $inside;
        }
        return $inside;
    }

    private function record(GpsLog $log, ?int $driverId, string $type, int $severity, array $metrics): void
    {
        DriverBehaviorEvent::firstOrCreate(['gps_log_id' => $log->id, 'type' => $type], ['vehicle_id' => $log->vehicle_id, 'driver_id' => $driverId, 'severity' => $severity, 'metrics' => $metrics, 'occurred_at' => $log->recorded_at]);
    }
}

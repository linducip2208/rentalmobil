<?php

namespace App\Services\Gps;

use App\Models\GpsAlert;
use App\Models\GpsTracker;

class GpsAlertService
{
    public function evaluate(GpsTracker $tracker, array $position, \DateTimeInterface $occurredAt): void
    {
        $speed = is_numeric($position['speed'] ?? null) ? (float) $position['speed'] : null;
        $battery = is_numeric($position['battery_level'] ?? null) ? (int) $position['battery_level'] : null;

        if ($speed !== null && $tracker->speed_limit_kmh && $speed > $tracker->speed_limit_kmh) {
            $this->record($tracker, 'overspeed', 'critical', 'Kecepatan melebihi batas', "{$tracker->vehicle?->plate_number} terdeteksi {$speed} km/jam.", ['speed' => $speed, 'limit' => $tracker->speed_limit_kmh], $occurredAt);
        }
        if ($battery !== null && $battery <= 20) {
            $this->record($tracker, 'low_battery', $battery <= 10 ? 'critical' : 'warning', 'Baterai GPS rendah', "Baterai perangkat {$tracker->device_name} tersisa {$battery}%.", ['battery_level' => $battery], $occurredAt);
        }
        if ($tracker->geofence_latitude && $tracker->geofence_longitude && $tracker->geofence_radius_m) {
            $distance = $this->distanceMeters((float) $tracker->geofence_latitude, (float) $tracker->geofence_longitude, (float) $position['latitude'], (float) $position['longitude']);
            if ($distance > $tracker->geofence_radius_m) {
                $this->record($tracker, 'geofence_exit', 'critical', 'Kendaraan keluar geofence', "{$tracker->vehicle?->plate_number} berada {$this->formatDistance($distance)} dari titik aman.", ['distance_m' => round($distance), 'radius_m' => $tracker->geofence_radius_m], $occurredAt);
            }
        }
    }

    public function offline(GpsTracker $tracker): void
    {
        $this->record($tracker, 'offline', 'warning', 'GPS tidak mengirim data', "Perangkat {$tracker->device_name} tidak mengirim posisi sesuai ambang waktu.", [], now());
    }

    private function record(GpsTracker $tracker, string $type, string $severity, string $title, string $message, array $context, \DateTimeInterface $occurredAt): void
    {
        $bucket = now()->format($type === 'offline' ? 'Y-m-d-H' : 'Y-m-d-H-i');
        GpsAlert::firstOrCreate(['deduplication_key' => hash('sha256', "{$tracker->id}|{$type}|{$bucket}")], [
            'gps_tracker_id' => $tracker->id, 'vehicle_id' => $tracker->vehicle_id, 'type' => $type,
            'severity' => $severity, 'title' => $title, 'message' => $message, 'context' => $context, 'occurred_at' => $occurredAt,
        ]);
    }

    private function distanceMeters(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earth = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;

        return $earth * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    private function formatDistance(float $meters): string
    {
        return $meters >= 1000 ? round($meters / 1000, 1).' km' : round($meters).' m';
    }
}

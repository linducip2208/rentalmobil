<?php

namespace App\Services;

use App\Models\Location;
use App\Models\SystemSetting;

/**
 * Multi-city rental: biaya relokasi saat titik ambil dan titik kembali berbeda.
 * Pemilik aplikasi mengatur tarif via SystemSetting key "relocation_fees":
 * {"default_per_km": 2500, "pairs": {"1:2": 150000}}
 */
class MultiCityRelocationService
{
    public function calculateFee(Location $pickup, Location $return): float
    {
        if ($pickup->id === $return->id) {
            return 0.0;
        }

        $config = $this->config();

        $pairKey = $pickup->id . ':' . $return->id;
        $reverseKey = $return->id . ':' . $pickup->id;

        if (isset($config['pairs'][$pairKey])) {
            return (float) $config['pairs'][$pairKey];
        }

        if (isset($config['pairs'][$reverseKey])) {
            return (float) $config['pairs'][$reverseKey];
        }

        $perKm = (float) ($config['default_per_km'] ?? 0);

        if ($perKm <= 0 || !$pickup->latitude || !$pickup->longitude || !$return->latitude || !$return->longitude) {
            return 0.0;
        }

        $distanceKm = $this->haversineKm(
            (float) $pickup->latitude,
            (float) $pickup->longitude,
            (float) $return->latitude,
            (float) $return->longitude
        );

        return round($distanceKm * $perKm, 2);
    }

    public function haversineKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371.0;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;

        return round(2 * $earthRadius * asin(min(1.0, sqrt($a))), 1);
    }

    protected function config(): array
    {
        $raw = SystemSetting::get('relocation_fees');

        if (!$raw) {
            return [];
        }

        $decoded = is_string($raw) ? json_decode($raw, true) : $raw;

        return is_array($decoded) ? $decoded : [];
    }
}

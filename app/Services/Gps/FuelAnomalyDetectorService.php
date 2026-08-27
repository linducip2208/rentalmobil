<?php

namespace App\Services\Gps;

use App\Models\FuelAnomaly;
use App\Models\FuelLog;
use App\Models\GpsLog;
use App\Models\Vehicle;
use Illuminate\Support\Facades\DB;

/**
 * Deteksi kecurangan BBM: bandingkan liter yang diisi vs jarak GPS
 * antar pengisian. Konsumsi anomali (jauh lebih boros dari baseline)
 * ditandai sebagai kecurangan potensial.
 */
class FuelAnomalyDetectorService
{
    public function detectForVehicle(Vehicle $vehicle): array
    {
        $logs = FuelLog::where('vehicle_id', $vehicle->id)
            ->orderBy('fuel_date')
            ->get(['id', 'fuel_date', 'liters', 'odometer_km']);

        if ($logs->count() < 3) {
            return ['created' => 0];
        }

        $baseline = $this->baselineKmPerLiter($vehicle, $logs);

        if ($baseline <= 0) {
            return ['created' => 0, 'reason' => 'Baseline konsumsi belum cukup'];
        }

        $created = 0;
        $thresholdPct = (float) \App\Models\SystemSetting::get('fuel_anomaly_threshold_pct', 35);

        foreach ($logs as $index => $log) {
            if ($index === 0) {
                continue;
            }

            $prev = $logs[$index - 1];

            // Skip jika odometer tidak terisi valid.
            if (!$log->odometer_km || !$prev->odometer_km || $log->odometer_km <= $prev->odometer_km) {
                $distanceKm = $this->gpsDistanceKm($vehicle, $prev->fuel_date, $log->fuel_date);
            } else {
                $distanceKm = (float) ($log->odometer_km - $prev->odometer_km);
            }

            if ($distanceKm < 20) {
                continue;
            }

            $expectedLiters = round($distanceKm / $baseline, 2);
            $actualLiters = (float) $log->liters;
            $actualKmL = $actualLiters > 0 ? round($distanceKm / $actualLiters, 2) : 0;

            if ($expectedLiters <= 0) {
                continue;
            }

            $deviationPct = round(($actualLiters - $expectedLiters) / $expectedLiters * 100, 1);

            $alreadyFlagged = FuelAnomaly::where('fuel_log_id', $log->id)->exists();

            if (!$alreadyFlagged && $deviationPct >= $thresholdPct) {
                FuelAnomaly::create([
                    'vehicle_id' => $vehicle->id,
                    'fuel_log_id' => $log->id,
                    'distance_km' => $distanceKm,
                    'expected_liters' => $expectedLiters,
                    'actual_liters' => $actualLiters,
                    'baseline_km_per_liter' => $baseline,
                    'actual_km_per_liter' => $actualKmL,
                    'deviation_pct' => $deviationPct,
                    'status' => 'open',
                ]);
                $created++;
            }
        }

        return ['created' => $created, 'baseline_km_per_liter' => $baseline];
    }

    /**
     * Baseline = median km/liter historis yang wajar (tanpa outlier atas).
     */
    protected function baselineKmPerLiter(Vehicle $vehicle, $logs): float
    {
        $ratios = [];

        foreach ($logs as $index => $log) {
            if ($index === 0 || !$log->odometer_km || !$logs[$index - 1]->odometer_km) {
                continue;
            }

            $distance = (float) ($log->odometer_km - $logs[$index - 1]->odometer_km);
            $liters = (float) $log->liters;

            if ($distance > 30 && $liters > 0) {
                $ratios[] = $distance / $liters;
            }
        }

        if (count($ratios) < 2) {
            return (float) \App\Models\SystemSetting::get('default_fuel_efficiency_km_l', 10);
        }

        sort($ratios);
        $mid = intdiv(count($ratios), 2);

        // Ambil separuh bawah median — konservatif agar tidak false positive berlebihan.
        $conservativeRatios = array_slice($ratios, 0, max(1, $mid));

        return round(array_sum($conservativeRatios) / count($conservativeRatios), 2);
    }

    protected function gpsDistanceKm(Vehicle $vehicle, $fromDate, $toDate): float
    {
        $points = GpsLog::whereHas('tracker', fn ($q) => $q->where('vehicle_id', $vehicle->id))
            ->whereBetween('logged_at', [\Carbon\Carbon::parse($fromDate)->startOfDay(), \Carbon\Carbon::parse($toDate)->endOfDay()])
            ->orderBy('logged_at')
            ->get(['latitude', 'longitude']);

        $km = 0.0;

        for ($i = 1; $i < $points->count(); $i++) {
            $a = $points[$i - 1];
            $b = $points[$i];
            $km += $this->haversine((float) $a->latitude, (float) $a->longitude, (float) $b->latitude, (float) $b->longitude);
        }

        return round($km, 1);
    }

    protected function haversine(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;

        return 6371.0 * 2 * asin(min(1.0, sqrt($a)));
    }
}

<?php

namespace App\Console\Commands;

use App\Models\Vehicle;
use App\Services\Gps\FuelAnomalyDetectorService;
use Illuminate\Console\Command;

class DetectFuelAnomalies extends Command
{
    protected $signature = 'gps:detect-fuel-anomalies {--vehicle= : ID kendaraan spesifik}';

    protected $description = 'Deteksi anomali konsumsi BBM vs jarak GPS (potensi kecurangan bensin)';

    public function handle(): int
    {
        $service = app(FuelAnomalyDetectorService::class);

        $vehicles = $this->option('vehicle')
            ? Vehicle::whereKey((int) $this->option('vehicle'))->get()
            : Vehicle::where('is_active', true)->get();

        $total = 0;

        foreach ($vehicles as $vehicle) {
            $result = $service->detectForVehicle($vehicle);
            if (($result['created'] ?? 0) > 0) {
                $total += $result['created'];
                $this->warn("[{$vehicle->plate_number}] {$result['created']} anomali terdeteksi (baseline {$result['baseline_km_per_liter']} km/L)");
            }
        }

        $this->info("Total anomali baru: {$total} dari {$vehicles->count()} kendaraan.");

        return self::SUCCESS;
    }
}

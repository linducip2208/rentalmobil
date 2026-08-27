<?php

namespace App\Console\Commands;

use App\Models\AccountingPeriod;
use App\Models\Vehicle;
use App\Services\VehicleDepreciationService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class RunVehicleDepreciation extends Command
{
    protected $signature = 'finance:run-depreciation {--period=}';

    protected $description = 'Post depresiasi garis lurus kendaraan untuk periode akuntansi';

    public function handle(VehicleDepreciationService $service): int
    {
        $date = $this->option('period') ? Carbon::parse($this->option('period')) : now();
        $period = AccountingPeriod::query()->whereDate('start_date', '<=', $date)->whereDate('end_date', '>=', $date)->first();
        if (! $period) {
            $this->error('Periode akuntansi tidak ditemukan.');

            return self::FAILURE;
        }
        $count = 0;
        Vehicle::query()->whereNotNull('purchase_price')->where('purchase_price', '>', 0)->whereNotNull('useful_life_months')->each(function (Vehicle $vehicle) use ($service, $period, &$count) {
            try {
                $service->post($vehicle, $period);
                $count++;
            } catch (\RuntimeException $e) {
                $this->warn($vehicle->plate_number.': '.$e->getMessage());
            }
        });
        $this->info("Depresiasi diproses untuk {$count} kendaraan.");

        return self::SUCCESS;
    }
}

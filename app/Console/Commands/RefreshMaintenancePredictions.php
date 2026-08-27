<?php

namespace App\Console\Commands;

use App\Services\MaintenancePredictionService;
use Illuminate\Console\Command;

class RefreshMaintenancePredictions extends Command
{
    protected $signature = 'maintenance:predict';

    protected $description = 'Perbarui prediksi maintenance berdasarkan jadwal dan kilometer';

    public function handle(MaintenancePredictionService $s): int
    {
        $this->info('Prediksi diperbarui: '.$s->refresh());

        return self::SUCCESS;
    }
}

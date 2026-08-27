<?php

namespace App\Console\Commands;

use App\Services\DemandForecastService;
use Illuminate\Console\Command;

class PricingForecastDemand extends Command
{
    protected $signature = 'pricing:forecast-demand';

    protected $description = 'Generate demand forecast 30 hari ke depan per kategori & lokasi';

    public function handle(): int
    {
        $results = app(DemandForecastService::class)->generate();

        $this->info("Forecast dibuat: {$results['created']} baru, {$results['updated']} diperbarui.");

        return self::SUCCESS;
    }
}

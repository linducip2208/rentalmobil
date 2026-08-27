<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DistributeInvestorPayouts extends Command
{
    protected $signature = 'investors:distribute {--month= : Periode YYYY-MM (default: bulan lalu)}';

    protected $description = 'Hitung distribusi profit investor per kendaraan untuk satu periode';

    public function handle(): int
    {
$results = app(\App\Services\InvestorDistributionService::class)->distribute($this->option('month'));

$this->info("Periode {$results['period']}: {$results['created']} distribusi dibuat, {$results['skipped_existing']} sudah ada.");
return self::SUCCESS;
    }
}

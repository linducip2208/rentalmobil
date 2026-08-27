<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ScanFraudPatterns extends Command
{
    protected $signature = 'risk:scan-fraud-patterns';

    protected $description = 'Jalankan pemindaian pola fraud (dokumen duplikat, IP cluster, velocity)';

    public function handle(): int
    {
$results = app(\App\Services\FraudPatternDetectionService::class)->scan();

$this->info("Pattern dipindai: {$results['patterns']}, hit baru: {$results['hits_created']}.");

if ($results['hits_created'] > 0) {
    $this->warn("Ada {$results['hits_created']} temuan fraud baru — review di menu Risiko & Keamanan.");
}

return self::SUCCESS;
    }
}

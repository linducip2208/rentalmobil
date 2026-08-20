<?php

namespace App\Console\Commands;

use App\Services\AvailabilityEngine;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ReleaseExpiredHolds extends Command
{
    protected $signature = 'rental:release-holds';

    protected $description = 'Release booking holds that have passed their expiration time';

    public function handle(AvailabilityEngine $availabilityEngine): int
    {
        $releasedCount = $availabilityEngine->releaseExpiredHolds();

        $this->info("Released {$releasedCount} expired hold(s).");
        Log::info('ReleaseExpiredHolds: released ' . $releasedCount . ' holds');

        return Command::SUCCESS;
    }
}

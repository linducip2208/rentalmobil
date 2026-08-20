<?php

namespace App\Jobs;

use App\Models\GpsIntegration;
use App\Services\Gps\GpsSyncService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SyncGpsIntegration implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public array $backoff = [60, 180, 600];

    public function __construct(public int $integrationId) { $this->onQueue('gps'); }

    public function handle(GpsSyncService $service): void
    {
        $integration = GpsIntegration::active()->find($this->integrationId);
        if ($integration) $service->sync($integration);
    }
}

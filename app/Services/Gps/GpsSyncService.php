<?php

namespace App\Services\Gps;

use App\Models\GpsIntegration;

class GpsSyncService
{
    public function __construct(private GpsAdapterManager $manager, private GpsPositionIngestor $ingestor) {}

    public function sync(GpsIntegration $integration): array
    {
        $integration->update(['last_synced_at' => now(), 'last_error' => null]);
        try {
            $records = $this->manager->for($integration)->pullPositions($integration);
            $saved = 0;
            foreach ($records as $record) if (is_array($record) && $this->ingestor->ingest($integration, $record)) $saved++;
            $integration->update(['last_success_at' => now(), 'last_error' => null, 'failure_count' => 0, 'health_checked_at' => now(), 'health_status' => 'healthy']);
            return ['received' => count($records), 'saved' => $saved];
        } catch (\Throwable $e) {
            $failures = $integration->failure_count + 1;
            $integration->update(['last_error' => mb_substr($e->getMessage(), 0, 65000), 'failure_count' => $failures, 'health_checked_at' => now(), 'health_status' => $failures >= 3 ? 'down' : 'degraded']);
            throw $e;
        }
    }
}

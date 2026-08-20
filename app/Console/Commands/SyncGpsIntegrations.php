<?php

namespace App\Console\Commands;

use App\Models\GpsIntegration;
use App\Jobs\SyncGpsIntegration;
use Illuminate\Console\Command;

class SyncGpsIntegrations extends Command
{
    protected $signature = 'gps:sync {integration? : ID integrasi tertentu}';
    protected $description = 'Sinkronkan posisi dari seluruh integrasi GPS BYOK yang jatuh tempo';

    public function handle(): int
    {
        $query = GpsIntegration::active()->whereIn('adapter_format', ['rest_polling', 'traccar_compatible']);
        if ($id = $this->argument('integration')) $query->whereKey($id);
        foreach ($query->get() as $integration) {
            if (!$id && !$integration->isDue()) continue;
            SyncGpsIntegration::dispatch($integration->id);
            $this->info("#{$integration->id}: sinkronisasi masuk antrean GPS.");
        }
        return self::SUCCESS;
    }
}

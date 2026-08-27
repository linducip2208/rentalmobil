<?php

namespace App\Console\Commands;

use App\Models\GpsTracker;
use App\Services\Gps\GpsAlertService;
use Illuminate\Console\Command;

class MonitorGpsHealth extends Command
{
    protected $signature = 'gps:monitor-health {--minutes= : Ambang offline; default dari GPS_OFFLINE_THRESHOLD_MINUTES}';

    protected $description = 'Buat alert persisten untuk perangkat GPS yang berhenti mengirim data';

    public function handle(GpsAlertService $alerts): int
    {
        $minutes = $this->option('minutes') ?: config('gps.offline_threshold_minutes', 15);
        $cutoff = now()->subMinutes(max(5, (int) $minutes));
        GpsTracker::active()->where(fn ($q) => $q->whereNull('last_update_at')->orWhere('last_update_at', '<', $cutoff))->with('vehicle')->each(fn ($tracker) => $alerts->offline($tracker));

        return self::SUCCESS;
    }
}

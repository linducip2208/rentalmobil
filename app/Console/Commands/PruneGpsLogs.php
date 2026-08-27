<?php

namespace App\Console\Commands;

use App\Models\GpsLog;
use Illuminate\Console\Command;

class PruneGpsLogs extends Command
{
    protected $signature = 'gps:prune {--days= : Masa retensi; default dari GPS_LOG_RETENTION_DAYS}';

    protected $description = 'Hapus histori posisi GPS yang melewati masa retensi';

    public function handle(): int
    {
        $days = max(30, (int) ($this->option('days') ?: config('gps.log_retention_days', 180)));
        $deleted = GpsLog::where('recorded_at', '<', now()->subDays($days))->delete();
        $this->info("{$deleted} log GPS melewati retensi {$days} hari dihapus.");

        return self::SUCCESS;
    }
}

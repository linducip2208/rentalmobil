<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class AutoRefundDeposits extends Command
{
    protected $signature = 'finance:auto-refund-deposits';

    protected $description = 'Jadwalkan & proses refund deposit otomatis untuk order yang selesai mulus';

    public function handle(): int
    {
$scheduled = app(\App\Services\DepositAutoRefundService::class)->scheduleEligibleRefunds();
$processed = app(\App\Services\DepositAutoRefundService::class)->processDueRefunds();

$this->info("Dijadwalkan: {$scheduled['scheduled']}, diproses: {$processed['processed']}, menunggu manual: {$processed['pending_manual']}.");
return self::SUCCESS;
    }
}

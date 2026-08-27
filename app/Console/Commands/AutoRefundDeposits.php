<?php

namespace App\Console\Commands;

use App\Services\DepositAutoRefundService;
use Illuminate\Console\Command;

class AutoRefundDeposits extends Command
{
    protected $signature = 'finance:auto-refund-deposits';

    protected $description = 'Jadwalkan & proses refund deposit otomatis untuk order yang selesai mulus';

    public function handle(): int
    {
        $scheduled = app(DepositAutoRefundService::class)->scheduleEligibleRefunds();
        $processed = app(DepositAutoRefundService::class)->processDueRefunds();

        $this->info("Dijadwalkan: {$scheduled['scheduled']}, diproses: {$processed['processed']}, menunggu manual: {$processed['pending_manual']}.");

        return self::SUCCESS;
    }
}

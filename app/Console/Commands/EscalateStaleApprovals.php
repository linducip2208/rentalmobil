<?php

namespace App\Console\Commands;

use App\Services\ApprovalService;
use Illuminate\Console\Command;

class EscalateStaleApprovals extends Command
{
    protected $signature = 'approvals:escalate-stale';
    protected $description = 'Eskalasi otomatis persetujuan yang tertunda lebih dari 24 jam';

    public function handle(ApprovalService $service): int
    {
        $this->info('Persetujuan dieskalasi: '.$service->autoEscalateStale());
        return self::SUCCESS;
    }
}

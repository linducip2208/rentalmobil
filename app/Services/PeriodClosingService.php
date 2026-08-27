<?php

namespace App\Services;

use App\Models\AccountingPeriod;
use App\Models\AuditLog;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PeriodClosingService
{
    public function periodFor(CarbonInterface|string $date): ?AccountingPeriod
    {
        return AccountingPeriod::query()->whereDate('start_date', '<=', $date)->whereDate('end_date', '>=', $date)->first();
    }

    public function assertPostingAllowed(CarbonInterface|string $date): void
    {
        $period = $this->periodFor($date);
        if ($period && $period->status === 'closed') {
            throw new RuntimeException('Periode akuntansi sudah ditutup dan tidak menerima posting.');
        }
    }

    public function softClose(AccountingPeriod $period, ?string $notes = null): AccountingPeriod
    {
        return $this->changeStatus($period, 'soft_closed', $notes);
    }

    public function close(AccountingPeriod $period, ?string $notes = null): AccountingPeriod
    {
        return $this->changeStatus($period, 'closed', $notes);
    }

    public function reopen(AccountingPeriod $period): AccountingPeriod
    {
        return $this->changeStatus($period, 'open');
    }

    private function changeStatus(AccountingPeriod $period, string $status, ?string $notes = null): AccountingPeriod
    {
        return DB::transaction(function () use ($period, $status, $notes) {
            $period = AccountingPeriod::query()->lockForUpdate()->findOrFail($period->id);
            $old = $period->status;
            if ($old === $status) {
                return $period;
            }
            $period->update(['status' => $status, 'closed_by' => $status === 'open' ? null : auth()->id(), 'closed_at' => $status === 'open' ? null : now(), 'closing_notes' => $notes ?? $period->closing_notes]);
            AuditLog::create(['user_id' => auth()->id(), 'action' => 'accounting_period.'.$status, 'auditable_type' => $period->getMorphClass(), 'auditable_id' => $period->id, 'old_values' => ['status' => $old], 'new_values' => ['status' => $status]]);

            return $period;
        }, 3);
    }
}

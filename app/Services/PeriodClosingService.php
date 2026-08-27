<?php

namespace App\Services;

use App\Models\AccountingPeriod;
use Carbon\CarbonInterface;
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

    public function close(AccountingPeriod $period, ?string $notes = null): AccountingPeriod
    {
        $period->update(['status' => 'closed', 'closed_by' => auth()->id(), 'closed_at' => now(), 'closing_notes' => $notes]);

        return $period;
    }

    public function reopen(AccountingPeriod $period): AccountingPeriod
    {
        $period->update(['status' => 'open', 'closed_by' => null, 'closed_at' => null]);

        return $period;
    }
}

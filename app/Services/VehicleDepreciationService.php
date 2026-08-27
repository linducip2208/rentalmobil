<?php

namespace App\Services;

use App\Models\AccountingPeriod;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\Vehicle;
use App\Models\VehicleDepreciationRun;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class VehicleDepreciationService
{
    public function __construct(private readonly PeriodClosingService $periods) {}

    public function post(Vehicle $vehicle, AccountingPeriod $period): VehicleDepreciationRun
    {
        return DB::transaction(function () use ($vehicle, $period) {
            $existing = VehicleDepreciationRun::query()->where('vehicle_id', $vehicle->id)->where('accounting_period_id', $period->id)->first();
            if ($existing) {
                return $existing;
            }
            $this->periods->assertPostingAllowed($period->end_date);
            if ($vehicle->depreciation_method !== 'straight_line') {
                throw new RuntimeException('Metode depresiasi kendaraan tidak didukung.');
            }
            $amount = $vehicle->monthlyDepreciation();
            $remaining = max(0, (float) $vehicle->purchase_price - (float) $vehicle->residual_value - (float) $vehicle->accumulated_depreciation);
            $amount = min($amount, $remaining);
            if ($amount <= 0) {
                throw new RuntimeException('Kendaraan tidak memiliki nilai yang dapat didepresiasi.');
            }

            $expense = $vehicle->depreciation_expense_account_id ?: ChartOfAccount::where('code', '5500')->value('id');
            $accumulated = $vehicle->accumulated_depreciation_account_id ?: ChartOfAccount::where('code', '1590')->value('id');
            if (! $expense || ! $accumulated) {
                throw new RuntimeException('Akun depresiasi belum dikonfigurasi.');
            }

            $entry = JournalEntry::create(['posting_key' => "vehicle-depreciation:{$vehicle->id}:{$period->id}", 'date' => $period->end_date, 'description' => "Depresiasi {$vehicle->plate_number} periode {$period->period_number}/{$period->fiscal_year}", 'reference_type' => Vehicle::class, 'reference_id' => $vehicle->id, 'total_debit' => $amount, 'total_credit' => $amount, 'status' => 'posted', 'posted_by' => auth()->id()]);
            JournalLine::create(['journal_entry_id' => $entry->id, 'account_id' => $expense, 'description' => 'Beban depresiasi kendaraan', 'debit' => $amount, 'credit' => 0]);
            JournalLine::create(['journal_entry_id' => $entry->id, 'account_id' => $accumulated, 'description' => 'Akumulasi depresiasi kendaraan', 'debit' => 0, 'credit' => $amount]);
            $vehicle->increment('accumulated_depreciation', $amount);

            return VehicleDepreciationRun::create(['vehicle_id' => $vehicle->id, 'accounting_period_id' => $period->id, 'journal_entry_id' => $entry->id, 'amount' => $amount, 'posting_date' => $period->end_date]);
        }, 3);
    }
}

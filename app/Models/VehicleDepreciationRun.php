<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleDepreciationRun extends Model
{
    protected $fillable = ['vehicle_id', 'accounting_period_id', 'journal_entry_id', 'amount', 'posting_date'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'posting_date' => 'date'];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function accountingPeriod(): BelongsTo
    {
        return $this->belongsTo(AccountingPeriod::class);
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }
}

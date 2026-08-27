<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccountingPeriod extends Model
{
    protected $fillable = ['fiscal_year', 'period_number', 'start_date', 'end_date', 'status', 'closed_by', 'closed_at', 'closing_notes'];

    protected function casts(): array
    {
        return ['fiscal_year' => 'integer', 'period_number' => 'integer', 'start_date' => 'date', 'end_date' => 'date', 'closed_at' => 'datetime'];
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function depreciationRuns(): HasMany
    {
        return $this->hasMany(VehicleDepreciationRun::class);
    }
}

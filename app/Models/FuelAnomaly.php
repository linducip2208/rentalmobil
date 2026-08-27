<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FuelAnomaly extends Model
{
    protected $fillable = [
        'vehicle_id',
        'fuel_log_id',
        'distance_km',
        'expected_liters',
        'actual_liters',
        'baseline_km_per_liter',
        'actual_km_per_liter',
        'deviation_pct',
        'status',
        'notes',
        'reviewed_by',
    ];


    protected function casts(): array
    {
        return [
            'distance_km' => 'decimal:2',
            'expected_liters' => 'decimal:2',
            'actual_liters' => 'decimal:2',
            'baseline_km_per_liter' => 'decimal:2',
            'actual_km_per_liter' => 'decimal:2',
            'deviation_pct' => 'decimal:2',
        ];
    }

    public function vehicle(): BelongsTo { return $this->belongsTo(Vehicle::class); }

    public function fuelLog(): BelongsTo { return $this->belongsTo(FuelLog::class); }

    public function reviewedBy(): BelongsTo { return $this->belongsTo(User::class, 'reviewed_by'); }

    public function scopeOpen($query) { return $query->where('status', 'open'); }
}

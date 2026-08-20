<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FuelLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'logged_by',
        'fuel_date',
        'fuel_type',
        'liters',
        'cost',
        'odometer_km',
        'location',
        'receipt_url',
    ];

    protected function casts(): array
    {
        return [
            'fuel_date' => 'date',
            'liters' => 'decimal:2',
            'cost' => 'decimal:2',
            'odometer_km' => 'integer',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function loggedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'logged_by');
    }

    public function scopeByDateRange($query, $from, $to)
    {
        return $query->whereBetween('fuel_date', [$from, $to]);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('fuel_date', '>=', now()->subDays($days));
    }
}

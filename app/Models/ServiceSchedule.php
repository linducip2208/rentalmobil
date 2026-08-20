<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'service_type',
        'interval_km',
        'interval_days',
        'last_service_km',
        'last_service_date',
        'next_service_km',
        'next_service_date',
        'estimated_cost',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'interval_km' => 'integer',
            'interval_days' => 'integer',
            'last_service_km' => 'integer',
            'last_service_date' => 'date',
            'next_service_km' => 'integer',
            'next_service_date' => 'date',
            'estimated_cost' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDueSoon($query, int $days = 7)
    {
        return $query->where('is_active', true)
            ->where('next_service_date', '<=', now()->addDays($days));
    }

    public function scopeOverdue($query)
    {
        return $query->where('is_active', true)
            ->where('next_service_date', '<', now());
    }

    public function isDue(): bool
    {
        return $this->is_active && $this->next_service_date && $this->next_service_date->lte(now());
    }

    public function isDueByKm(int $currentKm): bool
    {
        return $this->is_active && $this->next_service_km && $currentKm >= $this->next_service_km;
    }
}

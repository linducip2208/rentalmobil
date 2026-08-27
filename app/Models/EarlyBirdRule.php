<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EarlyBirdRule extends Model
{
    protected $fillable = [
        'name',
        'min_lead_days',
        'max_lead_days',
        'discount_type',
        'discount_value',
        'category_id',
        'location_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'min_lead_days' => 'integer',
            'max_lead_days' => 'integer',
            'discount_value' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function scopeForVehicle($query, Vehicle $vehicle)
    {
        return $query->where('is_active', true)->where(fn ($q) => $q->whereNull('category_id')->orWhere('category_id', $vehicle->category_id))->where(fn ($q) => $q->whereNull('location_id')->orWhere('location_id', $vehicle->location_id));
    }

    public function matchesLeadDays(int $days): bool
    {
        if ($days < $this->min_lead_days) {
            return false;
        }

return $this->max_lead_days === null || $days <= $this->max_lead_days;
    }
}

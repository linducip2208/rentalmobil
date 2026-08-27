<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FlashSale extends Model
{
    protected $fillable = [
        'name',
        'discount_type',
        'discount_value',
        'starts_at',
        'ends_at',
        'max_redemptions',
        'used_count',
        'category_id',
        'location_id',
        'vehicle_ids',
        'is_active',
    ];


    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'used_count' => 'integer',
            'vehicle_ids' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo { return $this->belongsTo(Category::class); }

    public function location(): BelongsTo { return $this->belongsTo(Location::class); }

    public function scopeLive($query) { return $query->where('is_active', true)->where('starts_at', '<=', now())->where('ends_at', '>', now()); }

    public function coversVehicle(\App\Models\Vehicle $vehicle): bool { if (!$this->is_active || now()->lt($this->starts_at) || now()->gte($this->ends_at)) return false; if ($this->max_redemptions !== null && $this->used_count >= $this->max_redemptions) return false; if ($this->category_id && $this->category_id !== $vehicle->category_id) return false; if ($this->location_id && $this->location_id !== $vehicle->location_id) return false; if (filled($this->vehicle_ids) && !in_array($vehicle->id, $this->vehicle_ids)) return false; return true; }

    public function discountFor(float $amount): float { $d = $this->discount_type === 'percentage' ? round($amount * ((float) $this->discount_value / 100), 2) : min((float) $this->discount_value, $amount); return round($d, 2); }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SurgePricingRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_category_id',
        'location_id',
        'name',
        'multiplier',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'days_of_week',
        'priority',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'multiplier' => 'decimal:2',
            'start_date' => 'date',
            'end_date' => 'date',
            'days_of_week' => 'array',
            'priority' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function vehicleCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'vehicle_category_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeValid($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('start_date')
                    ->orWhere('start_date', '<=', now()->toDateString());
            })
            ->where(function ($q) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', now()->toDateString());
            });
    }

    public function scopeForDate($query, $date)
    {
        return $query->where(function ($q) use ($date) {
            $q->whereNull('start_date')->orWhere('start_date', '<=', $date);
        })->where(function ($q) use ($date) {
            $q->whereNull('end_date')->orWhere('end_date', '>=', $date);
        });
    }

    public function isActiveForDateTime(\DateTime $dateTime): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $date = $dateTime->toDateString();
        if ($this->start_date && $this->start_date->gt($date)) {
            return false;
        }
        if ($this->end_date && $this->end_date->lt($date)) {
            return false;
        }

        if (! empty($this->days_of_week) && ! in_array($dateTime->dayOfWeekIso, $this->days_of_week)) {
            return false;
        }

        if ($this->start_time && $this->end_time) {
            $time = $dateTime->format('H:i:s');
            if ($this->start_time <= $this->end_time) {
                if ($time < $this->start_time || $time > $this->end_time) {
                    return false;
                }
            } else {
                if ($time < $this->start_time && $time > $this->end_time) {
                    return false;
                }
            }
        }

        return true;
    }
}

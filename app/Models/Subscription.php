<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model
{
    protected $fillable = [
        'customer_id',
        'vehicle_id',
        'plan_name',
        'billing_cycle',
        'price_per_cycle',
        'start_date',
        'current_period_end',
        'auto_renew',
        'included_km_per_cycle',
        'overage_km_rate',
        'status',
        'cancelled_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'current_period_end' => 'date',
            'auto_renew' => 'boolean',
            'included_km_per_cycle' => 'integer',
            'overage_km_rate' => 'decimal:2',
            'price_per_cycle' => 'decimal:2',
            'cancelled_at' => 'date',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function isDueForBilling(): bool
    {
        return $this->status === 'active'
            && $this->auto_renew
            && ($this->current_period_end === null || $this->current_period_end->lte(now()));
    }

    public function nextPeriodEnd(): CarbonInterface
    {
        $base = $this->current_period_end ?? now();

        return match ($this->billing_cycle) {
            'quarterly' => $base->copy()->addMonths(3),
            'yearly' => $base->copy()->addYear(),
            default => $base->copy()->addMonth(),
        };
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PromoVoucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'discount_type',
        'discount_value',
        'min_rental_days',
        'max_discount',
        'usage_limit',
        'used_count',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'discount_value' => 'decimal:2',
            'min_rental_days' => 'integer',
            'max_discount' => 'decimal:2',
            'usage_limit' => 'integer',
            'used_count' => 'integer',
            'start_date' => 'date',
            'end_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function usages(): HasMany
    {
        return $this->hasMany(VoucherUsage::class, 'voucher_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeValid($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('start_date')->orWhere('start_date', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', now());
            });
    }

    public function scopeExpired($query)
    {
        return $query->where('end_date', '<', now());
    }

    public function isValid(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->start_date && $this->start_date->isFuture()) {
            return false;
        }

        if ($this->end_date && $this->end_date->isPast()) {
            return false;
        }

        if ($this->usage_limit && $this->used_count >= $this->usage_limit) {
            return false;
        }

        return true;
    }

    public function calculateDiscount(float $amount): float
    {
        if (! $this->isValid()) {
            return 0;
        }

        if ($this->min_rental_days) {
            return 0;
        }

        if ($this->discount_type === 'percentage') {
            $discount = $amount * ($this->discount_value / 100);
            if ($this->max_discount && $discount > $this->max_discount) {
                $discount = $this->max_discount;
            }

            return $discount;
        }

        return min($this->discount_value, $amount);
    }
}

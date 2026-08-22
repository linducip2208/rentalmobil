<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CorporateAccount extends Model
{
    protected $fillable = [
        'name',
        'contact_name',
        'contact_email',
        'contact_phone',
        'tax_id',
        'address',
        'credit_limit',
        'payment_terms_days',
        'discount_percent',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'credit_limit' => 'decimal:2',
            'discount_percent' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /** Total piutang belum dibayar dari seluruh invoice customer milik akun ini. */
    public function outstandingBalance(): float
    {
        $customerIds = $this->customers()->pluck('id');

        if ($customerIds->isEmpty()) {
            return 0.0;
        }

        return (float) Invoice::whereIn('customer_id', $customerIds)
            ->whereNotIn('status', ['paid', 'cancelled', 'voided'])
            ->sum('balance_due');
    }

    public function availableCredit(): float
    {
        return max(0, (float) $this->credit_limit - $this->outstandingBalance());
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class LoyaltyLedger extends Model
{
    protected $fillable = [
        'customer_id',
        'type',
        'points',
        'balance_after',
        'description',
        'reference_type',
        'reference_id',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'points' => 'integer',
            'balance_after' => 'integer',
            'expires_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}

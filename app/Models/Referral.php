<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Referral extends Model
{
    protected $fillable = [
        'referrer_customer_id',
        'code',
        'referred_email',
        'referred_name',
        'referred_customer_id',
        'reward_type',
        'reward_value',
        'status',
        'completed_at',
    ];


    protected function casts(): array
    {
        return [
            'reward_value' => 'integer',
            'completed_at' => 'datetime',
        ];
    }

    public function referrerCustomer(): BelongsTo { return $this->belongsTo(Customer::class, 'referrer_customer_id'); }

    public function referredCustomer(): BelongsTo { return $this->belongsTo(Customer::class, 'referred_customer_id'); }
}

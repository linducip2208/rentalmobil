<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DamageComparison extends Model
{
    protected $fillable = [
        'rental_order_id',
        'checkout_handover_id',
        'return_handover_id',
        'provider_id',
        'analysis',
        'new_damages_count',
        'estimated_cost',
        'status',
        'completed_at',
    ];


    protected function casts(): array
    {
        return [
            'analysis' => 'array',
            'new_damages_count' => 'integer',
            'estimated_cost' => 'decimal:2',
            'completed_at' => 'datetime',
        ];
    }

    public function rentalOrder(): BelongsTo { return $this->belongsTo(RentalOrder::class); }

    public function checkoutHandover(): BelongsTo { return $this->belongsTo(HandoverRecord::class, 'checkout_handover_id'); }

    public function returnHandover(): BelongsTo { return $this->belongsTo(HandoverRecord::class, 'return_handover_id'); }

    public function provider(): BelongsTo { return $this->belongsTo(Provider::class); }
}

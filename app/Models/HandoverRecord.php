<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HandoverRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'rental_order_id',
        'vehicle_id',
        'customer_id',
        'staff_id',
        'type',
        'odometer_readings',
        'fuel_level',
        'body_condition',
        'interior_condition',
        'accessories',
        'checklist',
        'photos',
        'customer_signature_url',
        'staff_signature_url',
        'notes',
        'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'odometer_readings' => 'array',
            'fuel_level' => 'integer',
            'accessories' => 'array',
            'checklist' => 'array',
            'photos' => 'array',
            'recorded_at' => 'datetime',
        ];
    }

    public function rentalOrder(): BelongsTo
    {
        return $this->belongsTo(RentalOrder::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function scopeOutbound($query)
    {
        return $query->where('type', 'outbound');
    }

    public function scopeInbound($query)
    {
        return $query->where('type', 'inbound');
    }

    public function isOutbound(): bool
    {
        return $this->type === 'outbound';
    }

    public function isInbound(): bool
    {
        return $this->type === 'inbound';
    }
}

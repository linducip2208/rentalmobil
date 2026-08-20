<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Delivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'rental_order_id',
        'driver_id',
        'vehicle_id',
        'from_location_id',
        'to_location_id',
        'delivery_type',
        'status',
        'scheduled_date',
        'actual_date',
        'start_km',
        'end_km',
        'distance_km',
        'estimated_duration',
        'actual_duration',
        'notes',
        'proof_photos',
        'customer_signature',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_date' => 'datetime',
            'actual_date' => 'datetime',
            'start_km' => 'decimal:1',
            'end_km' => 'decimal:1',
            'distance_km' => 'decimal:1',
            'estimated_duration' => 'integer',
            'actual_duration' => 'integer',
            'proof_photos' => 'array',
        ];
    }

    public function rentalOrder(): BelongsTo
    {
        return $this->belongsTo(RentalOrder::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function fromLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'from_location_id');
    }

    public function toLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'to_location_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }
}

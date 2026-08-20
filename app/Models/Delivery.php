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
        'type',
        'status',
        'scheduled_date',
        'scheduled_time',
        'actual_date',
        'actual_time',
        'from_address',
        'to_address',
        'notes',
        'photos',
        'signature_url',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_date' => 'date',
            'actual_date' => 'date',
            'photos' => 'array',
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

    public function scopeDispatched($query)
    {
        return $query->where('status', 'dispatched');
    }

    public function scopeInTransit($query)
    {
        return $query->where('status', 'in_transit');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeDeliveries($query)
    {
        return $query->where('type', 'delivery');
    }

    public function scopePickups($query)
    {
        return $query->where('type', 'pickup');
    }
}

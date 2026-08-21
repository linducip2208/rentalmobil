<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Booking extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'booking_number',
        'customer_id',
        'vehicle_id',
        'pickup_location_id',
        'return_location_id',
        'driver_id',
        'start_date',
        'end_date',
        'estimated_return_date',
        'rental_type',
        'duration_days',
        'daily_rate_snapshot',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'total_amount',
        'deposit_amount',
        'status',
        'cancellation_reason',
        'cancelled_at',
        'confirmed_at',
        'hold_expires_at',
        'notes',
        'source',
        'addon_ids',
        'pricing_snapshot',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'estimated_return_date' => 'date',
        'duration_days' => 'integer',
        'daily_rate_snapshot' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
        'cancelled_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'hold_expires_at' => 'datetime',
        'addon_ids' => 'array',
        'pricing_snapshot' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($booking) {
            if (empty($booking->booking_number)) {
                $booking->booking_number = 'BKG-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));
            }
        });
    }

    // Relationships
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function pickupLocation()
    {
        return $this->belongsTo(Location::class, 'pickup_location_id');
    }

    public function returnLocation()
    {
        return $this->belongsTo(Location::class, 'return_location_id');
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function quotations()
    {
        return $this->hasMany(Quotation::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'expired')->orWhere(function ($q) {
            $q->where('status', 'hold')->where('hold_expires_at', '<', now());
        });
    }
}

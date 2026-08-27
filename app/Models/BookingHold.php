<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingHold extends Model
{
    protected $fillable = [
        'vehicle_id',
        'hold_token',
        'session_id',
        'start_date',
        'end_date',
        'expires_at',
        'status',
        'booking_id',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'expires_at' => 'datetime',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')->where('expires_at', '>', now());
    }
}

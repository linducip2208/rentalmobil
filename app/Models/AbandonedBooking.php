<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AbandonedBooking extends Model
{
    protected $fillable = [
        'session_id',
        'name',
        'email',
        'phone',
        'vehicle_id',
        'quote_snapshot',
        'last_step',
        'reminders_sent',
        'status',
        'recovered_booking_id',
        'last_activity_at',
    ];


    protected function casts(): array
    {
        return [
            'quote_snapshot' => 'array',
            'reminders_sent' => 'integer',
            'last_activity_at' => 'datetime',
        ];
    }

    public function vehicle(): BelongsTo { return $this->belongsTo(Vehicle::class); }

    public function recoveredBooking(): BelongsTo { return $this->belongsTo(Booking::class, 'recovered_booking_id'); }

    public function scopeOpen($query) { return $query->where('status', 'open'); }

    public function scopeStale($query, int $hours = 2) { return $query->where('last_activity_at', '<', now()->subHours($hours)); }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GroupBooking extends Model
{
    protected $fillable = [
        'code',
        'event_name',
        'contact_name',
        'contact_phone',
        'contact_email',
        'units_needed',
        'category_id',
        'location_id',
        'start_date',
        'end_date',
        'quoted_total',
        'status',
        'notes',
    ];


    protected function casts(): array
    {
        return [
            'units_needed' => 'integer',
            'start_date' => 'date',
            'end_date' => 'date',
            'quoted_total' => 'decimal:2',
        ];
    }

    protected static function boot(): void { parent::boot(); static::creating(function (self $gb) { if (blank($gb->code)) { $gb->code = 'GRP-' . now()->format('ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(4)); } }); }

    public function category(): BelongsTo { return $this->belongsTo(Category::class); }

    public function location(): BelongsTo { return $this->belongsTo(Location::class); }

    public function bookings(): HasMany { return $this->hasMany(Booking::class); }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Driver extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'location_id',
        'name',
        'phone',
        'email',
        'address',
        'ktp_number',
        'sim_type',
        'sim_number',
        'sim_expiry',
        'photo',
        'rating',
        'total_trips',
        'is_available',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sim_expiry' => 'date',
            'rating' => 'decimal:2',
            'total_trips' => 'integer',
            'is_available' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(DriverRating::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAvailable($query)
    {
        return $query->where('is_available', true)->where('is_active', true);
    }

    public function hasValidSim(): bool
    {
        return $this->sim_expiry && $this->sim_expiry->isFuture();
    }
}

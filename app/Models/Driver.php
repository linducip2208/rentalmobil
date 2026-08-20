<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Driver extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'license_number',
        'license_expiry',
        'phone',
        'address',
        'emergency_contact',
        'status',
        'is_active',
        'rating',
        'total_trips',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'license_expiry' => 'date',
            'is_active' => 'boolean',
            'rating' => 'decimal:2',
            'total_trips' => 'integer',
        ];
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(RentalOrder::class, 'driver_id');
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(Delivery::class, 'driver_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')->where('is_active', true);
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'active')->where('is_active', true);
    }

    public function isAvailable(): bool
    {
        return $this->status === 'active' && $this->is_active;
    }

    public function hasValidLicense(): bool
    {
        return $this->license_expiry && $this->license_expiry->isFuture();
    }
}

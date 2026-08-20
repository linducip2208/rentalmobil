<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverRating extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'rental_order_id',
        'customer_id',
        'rating',
        'punctuality',
        'driving_skill',
        'attitude',
        'vehicle_cleanliness',
        'comment',
        'is_anonymous',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'punctuality' => 'integer',
            'driving_skill' => 'integer',
            'attitude' => 'integer',
            'vehicle_cleanliness' => 'integer',
            'is_anonymous' => 'boolean',
        ];
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function rentalOrder(): BelongsTo
    {
        return $this->belongsTo(RentalOrder::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function scopeForDriver($query, int $driverId)
    {
        return $query->where('driver_id', $driverId);
    }

    public function getAverageDetailAttribute(): float
    {
        $details = array_filter([
            $this->punctuality,
            $this->driving_skill,
            $this->attitude,
            $this->vehicle_cleanliness,
        ]);

        return $details ? round(array_sum($details) / count($details), 1) : 0.0;
    }
}

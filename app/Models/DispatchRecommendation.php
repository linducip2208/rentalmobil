<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DispatchRecommendation extends Model
{
    protected $fillable = [
        'delivery_id',
        'recommended_driver_id',
        'recommended_vehicle_id',
        'score',
        'reasons',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'decimal:2',
            'reasons' => 'array',
        ];
    }

    public function delivery(): BelongsTo
    {
        return $this->belongsTo(Delivery::class);
    }

    public function recommendedDriver(): BelongsTo
    {
        return $this->belongsTo(Driver::class, 'recommended_driver_id');
    }

    public function recommendedVehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'recommended_vehicle_id');
    }
}

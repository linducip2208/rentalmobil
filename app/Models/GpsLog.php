<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GpsLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'gps_tracker_id',
        'external_event_id',
        'payload_hash',
        'latitude',
        'longitude',
        'speed',
        'heading',
        'accuracy',
        'battery_level',
        'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'speed' => 'decimal:2',
            'heading' => 'integer',
            'accuracy' => 'decimal:2',
            'battery_level' => 'integer',
            'recorded_at' => 'datetime',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function tracker(): BelongsTo
    {
        return $this->belongsTo(GpsTracker::class, 'gps_tracker_id');
    }

    public function scopeRecent($query, int $minutes = 60)
    {
        return $query->where('recorded_at', '>=', now()->subMinutes($minutes));
    }

    public function scopeLatest($query)
    {
        return $query->orderByDesc('recorded_at');
    }

    public function scopeByVehicle($query, int $vehicleId)
    {
        return $query->where('vehicle_id', $vehicleId);
    }
}

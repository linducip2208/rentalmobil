<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class GpsTracker extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'vehicle_id',
        'gps_integration_id',
        'device_id',
        'external_device_id',
        'device_name',
        'brand',
        'model',
        'sim_card_number',
        'sim_provider',
        'status',
        'is_active',
        'last_latitude',
        'last_longitude',
        'last_speed',
        'last_heading',
        'last_battery_level',
        'last_update_at',
        'installed_at',
        'notes',
        'metadata',
        'ingest_token_hash',
        'speed_limit_kmh',
        'geofence_latitude',
        'geofence_longitude',
        'geofence_radius_m',
    ];

    protected $hidden = ['ingest_token_hash'];

    public function setIngestTokenAttribute(?string $value): void
    {
        if (filled($value)) {
            $this->attributes['ingest_token_hash'] = hash('sha256', $value);
        }
    }

    protected function casts(): array
    {
        return [
            'last_latitude' => 'decimal:7',
            'last_longitude' => 'decimal:7',
            'last_speed' => 'decimal:2',
            'geofence_latitude' => 'decimal:7',
            'geofence_longitude' => 'decimal:7',
            'last_battery_level' => 'integer',
            'last_update_at' => 'datetime',
            'installed_at' => 'datetime',
            'is_active' => 'boolean',
            'metadata' => 'array',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function integration(): BelongsTo
    {
        return $this->belongsTo(GpsIntegration::class, 'gps_integration_id');
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(GpsAlert::class);
    }

    public function commands(): HasMany
    {
        return $this->hasMany(GpsCommand::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where('status', 'active');
    }

    public function scopeOnline($query, int $minutes = 10)
    {
        return $query->where('is_active', true)
            ->where('last_update_at', '>=', now()->subMinutes($minutes));
    }

    public function isOnline(): bool
    {
        return $this->is_active
            && $this->last_update_at
            && $this->last_update_at->diffInMinutes(now()) <= 10;
    }

    public function updateLocation(float $lat, float $lng, ?float $speed = null, ?int $heading = null, ?int $battery = null): void
    {
        $this->update([
            'last_latitude' => $lat,
            'last_longitude' => $lng,
            'last_speed' => $speed,
            'last_heading' => $heading,
            'last_battery_level' => $battery,
            'last_update_at' => now(),
        ]);
    }

    public function acceptsToken(?string $token): bool
    {
        return filled($token)
            && filled($this->ingest_token_hash)
            && hash_equals($this->ingest_token_hash, hash('sha256', $token));
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Crypt;

class Provider extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'api_format',
        'base_url',
        'api_key',
        'api_key_encrypted',
        'extra_headers',
        'config',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'extra_headers' => 'array',
            'config' => 'array',
            'is_active' => 'boolean',
        ];
    }

    protected $hidden = [
        'api_key_encrypted',
    ];

    public function setApiKeyAttribute(?string $value): void
    {
        $this->attributes['api_key_encrypted'] = filled($value) ? Crypt::encryptString($value) : null;
    }

    public function getApiKeyAttribute(): ?string
    {
        if (blank($this->api_key_encrypted)) {
            return null;
        }

        try {
            return Crypt::decryptString($this->api_key_encrypted);
        } catch (\Throwable) {
            return null;
        }
    }

    public function credentials(): HasMany
    {
        return $this->hasMany(NotificationTemplate::class, 'provider_id');
    }

    public function notificationQueues(): HasMany
    {
        return $this->hasMany(NotificationQueue::class, 'provider_id');
    }

    public function gpsIntegration(): HasOne
    {
        return $this->hasOne(GpsIntegration::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }
}

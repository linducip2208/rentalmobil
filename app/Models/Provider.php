<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Provider extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'api_format',
        'base_url',
        'api_key_encrypted',
        'extra_headers',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'extra_headers' => 'array',
            'is_active' => 'boolean',
        ];
    }

    protected $hidden = [
        'api_key_encrypted',
    ];

    public function credentials(): HasMany
    {
        return $this->hasMany(NotificationTemplate::class, 'provider_id');
    }

    public function notificationQueues(): HasMany
    {
        return $this->hasMany(NotificationQueue::class, 'provider_id');
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

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Webhook extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'url',
        'secret',
        'events',
        'is_active',
        'headers',
        'last_triggered_at',
        'last_status_code',
        'failure_count',
    ];

    protected function casts(): array
    {
        return [
            'events' => 'array',
            'headers' => 'array',
            'is_active' => 'boolean',
            'last_triggered_at' => 'datetime',
            'last_status_code' => 'integer',
            'failure_count' => 'integer',
        ];
    }

    protected $hidden = [
        'secret',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForEvent($query, string $event)
    {
        return $query->where('is_active', true)
            ->whereRaw('JSON_CONTAINS(events, ?)', [json_encode($event)]);
    }

    public function listensToEvent(string $event): bool
    {
        return $this->is_active && in_array($event, $this->events ?? []);
    }
}

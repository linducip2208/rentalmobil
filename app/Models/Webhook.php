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
        'last_triggered_at',
    ];

    protected $hidden = [
        'secret',
    ];

    protected function casts(): array
    {
        return [
            'events' => 'array',
            'is_active' => 'boolean',
            'last_triggered_at' => 'datetime',
        ];
    }

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

    public function markTriggered(): void
    {
        $this->update(['last_triggered_at' => now()]);
    }
}

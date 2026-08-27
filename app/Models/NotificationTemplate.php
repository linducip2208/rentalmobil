<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider_id',
        'name',
        'event_type',
        'channel',
        'subject',
        'body',
        'variables',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'variables' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByChannel($query, string $channel)
    {
        return $query->where('channel', $channel);
    }

    public function scopeByEventType($query, string $eventType)
    {
        return $query->where('event_type', $eventType);
    }

    public function render(array $data = []): string
    {
        $body = $this->body;
        foreach ($data as $key => $value) {
            $body = str_replace("{{$key}}", $value, $body);
        }

        return $body;
    }
}

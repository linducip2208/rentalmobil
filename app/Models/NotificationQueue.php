<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class NotificationQueue extends Model
{
    use HasFactory;

    protected $fillable = [
        'notifiable_type',
        'notifiable_id',
        'provider_id',
        'template_id',
        'event_type',
        'channel',
        'subject',
        'body',
        'payload',
        'status',
        'scheduled_at',
        'sent_at',
        'failed_at',
        'error_message',
        'attempts',
        'max_attempts',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'scheduled_at' => 'datetime',
            'sent_at' => 'datetime',
            'failed_at' => 'datetime',
            'attempts' => 'integer',
            'max_attempts' => 'integer',
        ];
    }

    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(NotificationTemplate::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeSending($query)
    {
        return $query->where('status', 'sending');
    }

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeReady($query)
    {
        return $query->where('status', 'pending')
            ->where(function ($q) {
                $q->whereNull('scheduled_at')
                    ->orWhere('scheduled_at', '<=', now());
            });
    }

    public function scopeRetryable($query)
    {
        return $query->where('status', 'failed')
            ->whereColumn('attempts', '<', 'max_attempts');
    }

    public function markAsSent(): void
    {
        $this->update(['status' => 'sent', 'sent_at' => now()]);
    }

    public function markAsFailed(string $error): void
    {
        $this->update([
            'status' => 'failed',
            'failed_at' => now(),
            'error_message' => $error,
            'attempts' => $this->attempts + 1,
        ]);
    }
}

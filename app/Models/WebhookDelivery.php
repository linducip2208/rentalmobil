<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebhookDelivery extends Model
{
    protected $attributes = [
        'status' => 'pending',
        'attempts' => 0,
        'max_attempts' => 5,
    ];

    protected $fillable = [
        'webhook_id',
        'event',
        'payload',
        'status',
        'attempts',
        'max_attempts',
        'response_code',
        'response_body',
        'error_note',
        'delivered_at',
        'next_retry_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'attempts' => 'integer',
            'max_attempts' => 'integer',
            'response_code' => 'integer',
            'delivered_at' => 'datetime',
            'next_retry_at' => 'datetime',
        ];
    }

    public function webhook(): BelongsTo
    {
        return $this->belongsTo(Webhook::class);
    }
}

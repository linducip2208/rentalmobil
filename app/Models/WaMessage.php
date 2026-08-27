<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaMessage extends Model
{
    protected $fillable = [
        'wa_conversation_id',
        'direction',
        'body',
        'payload',
        'processed_at',
    ];


    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'processed_at' => 'datetime',
        ];
    }

    public function conversation(): BelongsTo { return $this->belongsTo(WaConversation::class, 'wa_conversation_id'); }
}

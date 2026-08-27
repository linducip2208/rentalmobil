<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiChatMessage extends Model
{
    protected $fillable = [
        'user_id',
        'role',
        'content',
        'tokens_used',
        'latency_ms',
        'model',
        'provider_id',
    ];


    public function user(): BelongsTo { return $this->belongsTo(User::class); }

    public function provider(): BelongsTo { return $this->belongsTo(Provider::class); }
}

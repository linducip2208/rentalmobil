<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WaConversation extends Model
{
    protected $fillable = [
        'phone',
        'name',
        'state',
        'context',
        'is_handed_over',
        'last_message_at',
    ];


    protected function casts(): array
    {
        return [
            'context' => 'array',
            'is_handed_over' => 'boolean',
            'last_message_at' => 'datetime',
        ];
    }

    public function messages(): HasMany { return $this->hasMany(WaMessage::class); }
}

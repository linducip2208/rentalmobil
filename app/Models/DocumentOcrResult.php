<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class DocumentOcrResult extends Model
{
    protected $fillable = [
        'documentable_type',
        'documentable_id',
        'provider_id',
        'document_kind',
        'extracted',
        'confidence',
        'status',
        'raw_response',
    ];

    protected function casts(): array
    {
        return [
            'extracted' => 'array',
            'raw_response' => 'array',
        ];
    }

    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }
}

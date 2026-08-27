<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EinvoiceSubmission extends Model
{
    protected $fillable = [
        'invoice_id',
        'provider_id',
        'submission_ref',
        'status',
        'payload',
        'response',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'response' => 'array',
            'submitted_at' => 'datetime',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }
}

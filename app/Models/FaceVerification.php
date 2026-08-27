<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FaceVerification extends Model
{
    protected $fillable = [
        'customer_id',
        'ktp_photo_url',
        'selfie_url',
        'provider_id',
        'match_score',
        'status',
        'context',
        'analysis',
        'checked_at',
    ];


    protected function casts(): array
    {
        return [
            'match_score' => 'decimal:2',
            'analysis' => 'array',
            'checked_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }

    public function provider(): BelongsTo { return $this->belongsTo(Provider::class); }
}

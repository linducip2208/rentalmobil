<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class FraudHit extends Model
{
    protected $fillable = [
        'fraud_pattern_id',
        'customer_id',
        'subject_type',
        'subject_id',
        'severity',
        'details',
        'status',
        'reviewed_by',
    ];


    protected function casts(): array
    {
        return [
            'severity' => 'integer',
            'details' => 'array',
        ];
    }

    public function pattern(): BelongsTo { return $this->belongsTo(FraudPattern::class, 'fraud_pattern_id'); }

    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }

    public function subject(): MorphTo { return $this->morphTo(); }

    public function reviewedBy(): BelongsTo { return $this->belongsTo(User::class, 'reviewed_by'); }

    public function scopeNew($query) { return $query->where('status', 'new'); }
}

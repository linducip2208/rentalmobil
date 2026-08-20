<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrustScoreLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'previous_score',
        'new_score',
        'change_amount',
        'reason',
        'reference_type',
        'reference_id',
        'changed_by',
        'changed_at',
    ];

    protected function casts(): array
    {
        return [
            'previous_score' => 'decimal:2',
            'new_score' => 'decimal:2',
            'change_amount' => 'decimal:2',
            'changed_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    public function reference()
    {
        if (!$this->reference_type || !$this->reference_id) {
            return null;
        }

        return $this->morphTo('reference');
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('changed_at', '>=', now()->subDays($days));
    }

    public function scopeIncreases($query)
    {
        return $query->where('change_amount', '>', 0);
    }

    public function scopeDecreases($query)
    {
        return $query->where('change_amount', '<', 0);
    }
}

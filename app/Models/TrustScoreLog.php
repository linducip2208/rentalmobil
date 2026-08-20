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
        'change_reason',
        'changed_by',
    ];

    protected function casts(): array
    {
        return [
            'previous_score' => 'integer',
            'new_score' => 'integer',
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

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeIncreases($query)
    {
        return $query->whereColumn('new_score', '>', 'previous_score');
    }

    public function scopeDecreases($query)
    {
        return $query->whereColumn('new_score', '<', 'previous_score');
    }

    public function getChangeAmountAttribute(): int
    {
        return $this->new_score - $this->previous_score;
    }
}

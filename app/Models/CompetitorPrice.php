<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompetitorPrice extends Model
{
    protected $fillable = [
        'competitor_name',
        'category_id',
        'city',
        'daily_rate',
        'observed_at',
        'source_url',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'daily_rate' => 'decimal:2',
            'observed_at' => 'date',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('observed_at', '>=', now()->subDays($days));
    }
}

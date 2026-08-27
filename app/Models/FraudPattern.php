<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FraudPattern extends Model
{
    protected $fillable = [
        'name',
        'pattern_type',
        'conditions',
        'action',
        'lookback_days',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'conditions' => 'array',
            'lookback_days' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function hits(): HasMany
    {
        return $this->hasMany(FraudHit::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReconMatchingRule extends Model
{
    protected $fillable = [
        'name',
        'match_field',
        'operator',
        'value',
        'priority',
        'auto_match',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'priority' => 'integer',
            'auto_match' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('priority');
    }
}

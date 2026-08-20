<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SitemapEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'url',
        'type',
        'priority',
        'change_frequency',
        'last_modified',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'priority' => 'decimal:2',
            'last_modified' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public static function generateForPage(string $url, string $type = 'page'): static
    {
        return static::firstOrCreate(
            ['url' => $url],
            [
                'type' => $type,
                'priority' => $type === 'page' ? '0.80' : '0.60',
                'change_frequency' => $type === 'page' ? 'weekly' : 'monthly',
                'last_modified' => now(),
                'is_active' => true,
            ]
        );
    }
}

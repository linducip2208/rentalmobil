<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Redirect extends Model
{
    use HasFactory;

    protected $fillable = [
        'from_path',
        'to_path',
        'status_code',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'status_code' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public static function resolve(string $path): ?string
    {
        $redirect = static::where('from_path', $path)
            ->where('is_active', true)
            ->first();

        return $redirect?->to_path;
    }

    public function getStatusCodeAttribute(int $value): int
    {
        return in_array($value, [301, 302, 307, 308]) ? $value : 301;
    }
}

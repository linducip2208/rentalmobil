<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class ExchangeRate extends Model
{
    protected $fillable = [
        'currency_code',
        'rate_to_idr',
        'effective_date',
        'source',
    ];


    protected function casts(): array
    {
        return [
            'rate_to_idr' => 'decimal:4',
            'effective_date' => 'date',
        ];
    }

    public static function latestFor(string $code): ?self { return static::where('currency_code', strtoupper($code))->orderByDesc('effective_date')->first(); }
}

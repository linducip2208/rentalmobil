<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DemandForecast extends Model
{
    protected $fillable = [
        'forecast_date',
        'category_id',
        'location_id',
        'predicted_occupancy',
        'confidence',
        'factors',
    ];

    protected function casts(): array
    {
        return [
            'forecast_date' => 'date',
            'predicted_occupancy' => 'float',
            'confidence' => 'decimal:2',
            'factors' => 'array',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function scopeForDate($query, string $date)
    {
        return $query->where('forecast_date', $date);
    }
}

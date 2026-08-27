<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverScorecard extends Model
{
    protected $fillable = [
        'driver_id',
        'period_start',
        'period_end',
        'overspeed_count',
        'harsh_brake_count',
        'harsh_acceleration_count',
        'long_idle_count',
        'geofence_violation_count',
        'trips',
        'avg_rating',
        'score',
        'rank_position',
    ];


    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'avg_rating' => 'decimal:2',
            'score' => 'decimal:2',
        ];
    }

    public function driver(): BelongsTo { return $this->belongsTo(Driver::class); }
}

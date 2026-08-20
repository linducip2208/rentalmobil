<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KmLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'logged_by',
        'log_date',
        'start_km',
        'end_km',
        'purpose',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'log_date' => 'date',
            'start_km' => 'integer',
            'end_km' => 'integer',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function loggedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'logged_by');
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('log_date', '>=', now()->subDays($days));
    }

    public function scopeLatest($query)
    {
        return $query->orderByDesc('log_date')->orderByDesc('id');
    }

    public function getDistanceAttribute(): int
    {
        return $this->end_km - $this->start_km;
    }
}

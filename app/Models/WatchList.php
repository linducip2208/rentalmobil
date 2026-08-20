<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WatchList extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'reason',
        'severity',
        'is_active',
        'added_by',
        'resolved_by',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'resolved_at' => 'datetime',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeResolved($query)
    {
        return $query->where('is_active', false)->whereNotNull('resolved_at');
    }

    public function scopeBySeverity($query, string $severity)
    {
        return $query->where('severity', $severity);
    }

    public function resolve(int $resolvedByUserId): void
    {
        $this->update([
            'is_active' => false,
            'resolved_by' => $resolvedByUserId,
            'resolved_at' => now(),
        ]);
    }
}

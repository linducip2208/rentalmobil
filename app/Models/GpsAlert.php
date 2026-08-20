<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GpsAlert extends Model
{
    protected $fillable = ['gps_tracker_id', 'vehicle_id', 'type', 'severity', 'deduplication_key', 'title', 'message', 'context', 'occurred_at', 'acknowledged_at', 'acknowledged_by', 'acknowledgement_note', 'resolved_at'];

    protected function casts(): array
    {
        return ['context' => 'array', 'occurred_at' => 'datetime', 'acknowledged_at' => 'datetime', 'resolved_at' => 'datetime'];
    }

    public function tracker(): BelongsTo { return $this->belongsTo(GpsTracker::class, 'gps_tracker_id'); }
    public function vehicle(): BelongsTo { return $this->belongsTo(Vehicle::class); }
    public function acknowledgedBy(): BelongsTo { return $this->belongsTo(User::class, 'acknowledged_by'); }
}

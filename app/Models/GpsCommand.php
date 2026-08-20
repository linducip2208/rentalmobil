<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GpsCommand extends Model
{
    protected $fillable = ['gps_tracker_id', 'requested_by', 'approved_by', 'command_name', 'parameters', 'status', 'reason', 'review_note', 'idempotency_key', 'approved_at', 'sent_at', 'response_body'];

    protected function casts(): array
    {
        return ['parameters' => 'array', 'approved_at' => 'datetime', 'sent_at' => 'datetime'];
    }

    public function tracker(): BelongsTo { return $this->belongsTo(GpsTracker::class, 'gps_tracker_id'); }
    public function requestedBy(): BelongsTo { return $this->belongsTo(User::class, 'requested_by'); }
    public function approvedBy(): BelongsTo { return $this->belongsTo(User::class, 'approved_by'); }
}

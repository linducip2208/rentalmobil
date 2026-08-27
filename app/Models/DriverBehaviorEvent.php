<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriverBehaviorEvent extends Model
{
    protected $fillable = ['vehicle_id', 'driver_id', 'gps_log_id', 'type', 'severity', 'metrics', 'occurred_at'];

    protected function casts(): array
    {
        return ['severity' => 'integer', 'metrics' => 'array', 'occurred_at' => 'datetime'];
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function gpsLog()
    {
        return $this->belongsTo(GpsLog::class);
    }
}

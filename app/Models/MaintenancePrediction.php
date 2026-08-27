<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaintenancePrediction extends Model
{
    protected $fillable = ['vehicle_id', 'prediction_type', 'predicted_date', 'predicted_km', 'confidence', 'factors', 'status'];

    protected function casts(): array
    {
        return ['predicted_date' => 'date', 'predicted_km' => 'integer', 'confidence' => 'decimal:2', 'factors' => 'array'];
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}

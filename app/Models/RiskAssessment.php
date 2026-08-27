<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiskAssessment extends Model
{
    protected $fillable = ['customer_id', 'assessable_type', 'assessable_id', 'fingerprint_hash', 'score', 'decision', 'matched_rules', 'context'];

    protected function casts(): array
    {
        return ['score' => 'integer', 'matched_rules' => 'array', 'context' => 'array'];
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function assessable()
    {
        return $this->morphTo();
    }
}

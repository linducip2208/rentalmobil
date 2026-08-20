<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InsurancePolicy extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'policy_number',
        'provider_name',
        'policy_type',
        'coverage_amount',
        'premium_amount',
        'start_date',
        'end_date',
        'status',
        'document_path',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'coverage_amount' => 'decimal:2',
            'premium_amount' => 'decimal:2',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeValid($query)
    {
        return $query->where('status', 'active')
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('end_date', '<', now());
    }

    public function isValid(): bool
    {
        return $this->status === 'active'
            && $this->start_date
            && $this->end_date
            && $this->start_date->lte(now())
            && $this->end_date->gte(now());
    }
}

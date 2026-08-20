<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReturnRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'rental_order_id',
        'actual_return_date',
        'actual_return_time',
        'return_location_id',
        'return_km',
        'return_fuel_level',
        'extra_km',
        'late_minutes',
        'body_condition',
        'interior_condition',
        'tire_condition',
        'has_damage',
        'damage_description',
        'photos',
        'late_charge',
        'fuel_charge',
        'damage_total',
        'other_charges',
        'total_charges',
        'deposit_refund',
        'inspector_id',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'actual_return_date' => 'date',
            'return_km' => 'integer',
            'return_fuel_level' => 'integer',
            'extra_km' => 'integer',
            'late_minutes' => 'integer',
            'has_damage' => 'boolean',
            'photos' => 'array',
            'late_charge' => 'decimal:2',
            'fuel_charge' => 'decimal:2',
            'damage_total' => 'decimal:2',
            'other_charges' => 'decimal:2',
            'total_charges' => 'decimal:2',
            'deposit_refund' => 'decimal:2',
            'approved_at' => 'datetime',
        ];
    }

    public function rentalOrder(): BelongsTo
    {
        return $this->belongsTo(RentalOrder::class);
    }

    public function returnLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'return_location_id');
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function damageReports(): HasMany
    {
        return $this->hasMany(DamageReport::class);
    }

    public function scopePendingReview($query)
    {
        return $query->where('status', 'pending_review');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeDisputed($query)
    {
        return $query->where('status', 'disputed');
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isDisputed(): bool
    {
        return $this->status === 'disputed';
    }
}

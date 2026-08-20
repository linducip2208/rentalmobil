<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReturnRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'rental_order_id',
        'return_date',
        'return_km',
        'fuel_level',
        'condition_notes',
        'body_condition',
        'interior_condition',
        'tire_condition',
        'has_damage',
        'damage_description',
        'damage_photos',
        'extra_charge',
        'late_minutes',
        'late_fee',
        'status',
        'inspector_id',
        'approved_by',
        'approved_at',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'return_date' => 'datetime',
            'return_km' => 'decimal:1',
            'has_damage' => 'boolean',
            'damage_photos' => 'array',
            'extra_charge' => 'decimal:2',
            'late_minutes' => 'integer',
            'late_fee' => 'decimal:2',
            'approved_at' => 'datetime',
        ];
    }

    public function rentalOrder(): BelongsTo
    {
        return $this->belongsTo(RentalOrder::class);
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function damageReports()
    {
        return $this->hasMany(DamageReport::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DamageReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'return_record_id',
        'rental_order_id',
        'vehicle_id',
        'customer_id',
        'reported_by',
        'damage_type',
        'location_on_vehicle',
        'severity',
        'description',
        'estimated_cost',
        'actual_cost',
        'charged_to_customer',
        'charge_amount',
        'photos',
        'status',
        'assessed_by',
        'assessed_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'estimated_cost' => 'decimal:2',
            'actual_cost' => 'decimal:2',
            'charged_to_customer' => 'boolean',
            'charge_amount' => 'decimal:2',
            'photos' => 'array',
            'assessed_at' => 'datetime',
        ];
    }

    public function returnRecord(): BelongsTo
    {
        return $this->belongsTo(ReturnRecord::class);
    }

    public function rentalOrder(): BelongsTo
    {
        return $this->belongsTo(RentalOrder::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function reportedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function assessedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assessed_by');
    }

    public function scopeReported($query)
    {
        return $query->where('status', 'reported');
    }

    public function scopeAssessed($query)
    {
        return $query->where('status', 'assessed');
    }

    public function scopeCharged($query)
    {
        return $query->where('status', 'charged');
    }

    public function scopeRepaired($query)
    {
        return $query->where('status', 'repaired');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    public function scopeCritical($query)
    {
        return $query->where('severity', 'critical');
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }
}

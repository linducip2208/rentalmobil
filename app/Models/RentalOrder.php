<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use App\Models\Concerns\BelongsToLocation;

class RentalOrder extends Model
{
    use HasFactory, SoftDeletes, BelongsToLocation;

    protected $fillable = [
        'order_number',
        'booking_id',
        'customer_id',
        'vehicle_id',
        'driver_id',
        'location_id',
        'start_date',
        'end_date',
        'actual_return_date',
        'rental_type',
        'duration_days',
        'daily_rate_snapshot',
        'subtotal',
        'addon_total',
        'discount_total',
        'tax_total',
        'late_fee',
        'damage_fee',
        'fuel_charge',
        'km_charge',
        'final_amount',
        'amount_paid',
        'balance_due',
        'deposit_amount',
        'amount_refunded',
        'purchase_order_number',
        'status',
        'payment_status',
        'pickup_km',
        'return_km',
        'pickup_fuel_level',
        'return_fuel_level',
        'notes',
        'internal_notes',
        'cancellation_reason',
        'cancelled_at',
        'dispatched_at',
        'checked_out_at',
        'checked_in_at',
        'completed_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'actual_return_date' => 'date',
            'duration_days' => 'integer',
            'daily_rate_snapshot' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'addon_total' => 'decimal:2',
            'discount_total' => 'decimal:2',
            'tax_total' => 'decimal:2',
            'late_fee' => 'decimal:2',
            'damage_fee' => 'decimal:2',
            'fuel_charge' => 'decimal:2',
            'km_charge' => 'decimal:2',
            'final_amount' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'balance_due' => 'decimal:2',
            'deposit_amount' => 'decimal:2',
            'amount_refunded' => 'decimal:2',
            'pickup_km' => 'integer',
            'return_km' => 'integer',
            'pickup_fuel_level' => 'integer',
            'return_fuel_level' => 'integer',
            'cancelled_at' => 'datetime',
            'dispatched_at' => 'datetime',
            'checked_out_at' => 'datetime',
            'checked_in_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (RentalOrder $model) {
            if (empty($model->order_number)) {
                $model->order_number = static::generateOrderNumber();
            }
        });
    }

    public static function generateOrderNumber(): string
    {
        $prefix = 'RO';
        $date = now()->format('Ymd');
        $last = static::withTrashed()
            ->where('order_number', 'like', "{$prefix}-{$date}-%")
            ->orderByDesc('order_number')
            ->value('order_number');

        if ($last) {
            $sequence = (int) substr($last, -4) + 1;
        } else {
            $sequence = 1;
        }

        return $prefix . '-' . $date . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(RentalOrderItem::class);
    }

    public function addons(): HasMany
    {
        return $this->hasMany(RentalOrderItem::class)->whereNotNull('addon_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function deposits(): HasMany
    {
        return $this->hasMany(Deposit::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(ReturnRecord::class);
    }

    public function damageReports(): HasMany
    {
        return $this->hasMany(DamageReport::class);
    }

    public function handovers(): HasMany
    {
        return $this->hasMany(HandoverRecord::class);
    }

    public function contract(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeReadyForPreparation($query)
    {
        return $query->where('status', 'ready_for_preparation');
    }

    public function scopePreparing($query)
    {
        return $query->where('status', 'preparing');
    }

    public function scopeCheckedOut($query)
    {
        return $query->where('status', 'checked_out');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue')
            ->orWhere(function ($q) {
                $q->where('status', 'active')
                    ->where('end_date', '<', Carbon::today());
            });
    }

    public function scopeReturnDue($query)
    {
        return $query->where('status', 'return_due');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    public function scopeUnpaid($query)
    {
        return $query->where('payment_status', '!=', 'paid');
    }

    public function isOverdue(): bool
    {
        return $this->status === 'active' && $this->end_date && $this->end_date->isPast();
    }

    public function hasOutstandingBalance(): bool
    {
        return (float) $this->final_amount > (float) $this->amount_paid;
    }

    public function getOutstandingBalanceAttribute(): float
    {
        return (float) $this->final_amount - (float) $this->amount_paid;
    }
}

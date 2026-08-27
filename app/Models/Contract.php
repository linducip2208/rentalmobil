<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contract extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'contract_number',
        'rental_order_id',
        'booking_id',
        'customer_id',
        'vehicle_id',
        'start_date',
        'end_date',
        'rental_type',
        'daily_rate',
        'total_amount',
        'deposit_amount',
        'km_limit',
        'fuel_policy',
        'usage_area',
        'late_policy',
        'damage_policy',
        'accident_policy',
        'loss_policy',
        'insurance_policy',
        'customer_signature_url',
        'staff_signature_url',
        'signed_at',
        'document_hash',
        'status',
        'version',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'daily_rate' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'deposit_amount' => 'decimal:2',
            'signed_at' => 'datetime',
            'version' => 'integer',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Contract $model) {
            if (empty($model->contract_number)) {
                $model->contract_number = static::generateContractNumber();
            }
        });
    }

    public static function generateContractNumber(): string
    {
        $prefix = 'CTR';
        $date = now()->format('Ymd');
        $last = static::withTrashed()
            ->where('contract_number', 'like', "{$prefix}-{$date}-%")
            ->orderByDesc('contract_number')
            ->value('contract_number');

        if ($last) {
            $sequence = (int) substr($last, -4) + 1;
        } else {
            $sequence = 1;
        }

        return $prefix.'-'.$date.'-'.str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    public function rentalOrder(): BelongsTo
    {
        return $this->belongsTo(RentalOrder::class);
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

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeSigned($query)
    {
        return $query->where('status', 'signed');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function isSigned(): bool
    {
        return $this->status === 'signed' || $this->status === 'completed';
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }
}

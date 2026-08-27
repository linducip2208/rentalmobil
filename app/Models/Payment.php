<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_number',
        'invoice_id',
        'rental_order_id',
        'customer_id',
        'payment_method_id',
        'amount',
        'payment_date',
        'payment_time',
        'reference_number',
        'proof_url',
        'status',
        'verified_by',
        'verified_at',
        'voided_at',
        'void_reason',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'payment_date' => 'date',
            'verified_at' => 'datetime',
            'voided_at' => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Payment $model) {
            if (empty($model->payment_number)) {
                $model->payment_number = static::generatePaymentNumber();
            }
        });
    }

    public static function generatePaymentNumber(): string
    {
        $prefix = 'PAY';
        $date = now()->format('Ymd');
        $last = static::where('payment_number', 'like', "{$prefix}-{$date}-%")
            ->orderByDesc('payment_number')
            ->value('payment_number');

        if ($last) {
            $sequence = (int) substr($last, -4) + 1;
        } else {
            $sequence = 1;
        }

        return $prefix.'-'.$date.'-'.str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function rentalOrder(): BelongsTo
    {
        return $this->belongsTo(RentalOrder::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeVerified($query)
    {
        return $query->where('status', 'verified');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeVoided($query)
    {
        return $query->where('status', 'voided');
    }

    public function scopeRefunded($query)
    {
        return $query->where('status', 'refunded');
    }

    public function isVerified(): bool
    {
        return $this->status === 'verified';
    }

    public function isVoided(): bool
    {
        return $this->status === 'voided';
    }
}

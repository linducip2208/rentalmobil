<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Deposit extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'rental_order_id',
        'booking_id',
        'amount',
        'deposit_status',
        'payment_method_id',
        'reference_number',
        'proof_url',
        'notes',
        'received_by',
        'received_at',
        'refunded_at',
        'refund_amount',
        'refund_method',
        'approved_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'received_at' => 'datetime',
            'refunded_at' => 'datetime',
            'refund_amount' => 'decimal:2',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function rentalOrder(): BelongsTo
    {
        return $this->belongsTo(RentalOrder::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopeExpected($query)
    {
        return $query->where('deposit_status', 'expected');
    }

    public function scopeReceived($query)
    {
        return $query->where('deposit_status', 'received');
    }

    public function scopeHeld($query)
    {
        return $query->where('deposit_status', 'held');
    }

    public function scopeRefunded($query)
    {
        return $query->where('deposit_status', 'refunded');
    }

    public function scopeForfeited($query)
    {
        return $query->where('deposit_status', 'forfeited');
    }

    public function scopeDisputed($query)
    {
        return $query->where('deposit_status', 'disputed');
    }

    public function isReceived(): bool
    {
        return $this->deposit_status === 'received';
    }

    public function isRefunded(): bool
    {
        return $this->deposit_status === 'refunded';
    }

    public function isHeld(): bool
    {
        return $this->deposit_status === 'held';
    }
}

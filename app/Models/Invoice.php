<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'invoice_number',
        'rental_order_id',
        'customer_id',
        'type',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'amount_paid',
        'balance_due',
        'due_date',
        'status',
        'paid_at',
        'notes',
        'pdf_path',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'balance_due' => 'decimal:2',
            'due_date' => 'date',
            'paid_at' => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Invoice $model) {
            if (empty($model->invoice_number)) {
                $model->invoice_number = static::generateInvoiceNumber();
            }
        });
    }

    public static function generateInvoiceNumber(): string
    {
        $prefix = 'INV';
        $date = now()->format('Ymd');
        $last = static::withTrashed()
            ->where('invoice_number', 'like', "{$prefix}-{$date}-%")
            ->orderByDesc('invoice_number')
            ->value('invoice_number');

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

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeIssued($query)
    {
        return $query->where('status', 'issued');
    }

    public function scopePartiallyPaid($query)
    {
        return $query->where('status', 'partially_paid');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue')
            ->orWhere(function ($q) {
                $q->where('status', '!=', 'paid')
                    ->where('status', '!=', 'cancelled')
                    ->where('status', '!=', 'voided')
                    ->where('due_date', '<', now());
            });
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function isOverdue(): bool
    {
        return ! in_array($this->status, ['paid', 'cancelled', 'voided'])
            && $this->due_date
            && $this->due_date->isPast();
    }

    public function hasOutstandingBalance(): bool
    {
        return (float) $this->total_amount > (float) $this->amount_paid;
    }
}

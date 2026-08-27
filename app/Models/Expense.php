<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'expense_number',
        'expense_category_id',
        'location_id',
        'user_id',
        'title',
        'description',
        'amount',
        'expense_date',
        'payment_method_id',
        'receipt_url',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'reference_type',
        'reference_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'expense_date' => 'date',
            'approved_at' => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (Expense $model) {
            if (empty($model->expense_number)) {
                $model->expense_number = static::generateExpenseNumber();
            }
        });
    }

    public static function generateExpenseNumber(): string
    {
        $prefix = 'EXP';
        $date = now()->format('ymd');
        $last = static::where('expense_number', 'like', "{$prefix}{$date}%")
            ->orderByDesc('expense_number')
            ->value('expense_number');

        if ($last) {
            $sequence = (int) substr($last, -4) + 1;
        } else {
            $sequence = 1;
        }

        return $prefix.$date.str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function reference()
    {
        if (! $this->reference_type || ! $this->reference_id) {
            return null;
        }

        return $this->morphTo('reference');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeByDateRange($query, $from, $to)
    {
        return $query->whereBetween('expense_date', [$from, $to]);
    }
}

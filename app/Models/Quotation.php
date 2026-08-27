<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quotation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'quotation_number',
        'customer_id',
        'customer_name',
        'customer_phone',
        'vehicle_id',
        'vehicle_name_snapshot',
        'start_date',
        'end_date',
        'rental_type',
        'duration_days',
        'daily_rate',
        'subtotal',
        'addon_total',
        'discount_amount',
        'tax_amount',
        'total_amount',
        'deposit_amount',
        'status',
        'valid_until',
        'notes',
        'terms_conditions',
        'lost_reason',
        'converted_to_booking_id',
        'sent_at',
        'viewed_at',
        'accepted_at',
        'rejected_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'duration_days' => 'integer',
            'daily_rate' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'addon_total' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'deposit_amount' => 'decimal:2',
            'valid_until' => 'date',
            'sent_at' => 'datetime',
            'viewed_at' => 'datetime',
            'accepted_at' => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Quotation $model) {
            if (empty($model->quotation_number)) {
                $model->quotation_number = static::generateNumber();
            }
        });
    }

    public static function generateNumber(): string
    {
        $prefix = 'QTN-'.date('Ym').'-';
        $last = static::withTrashed()
            ->where('quotation_number', 'like', $prefix.'%')
            ->latest('quotation_number')
            ->value('quotation_number');

        if ($last) {
            $sequence = (int) substr($last, -4) + 1;
        } else {
            $sequence = 1;
        }

        return $prefix.str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function convertedBooking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'converted_to_booking_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuotationItem::class);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopeViewed($query)
    {
        return $query->where('status', 'viewed');
    }

    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }

    public function scopeConverted($query)
    {
        return $query->where('status', 'converted');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['draft', 'sent', 'viewed']);
    }

    public function isExpired(): bool
    {
        return $this->valid_until && $this->valid_until->isPast() && ! in_array($this->status, ['accepted', 'converted', 'rejected']);
    }
}

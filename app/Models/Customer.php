<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'email',
        'phone',
        'address',
        'city',
        'province',
        'postal_code',
        'id_card_type',
        'id_card_number',
        'id_card_photo',
        'selfie_photo',
        'company_name',
        'company_address',
        'emergency_contact_name',
        'emergency_contact_phone',
        'trust_score',
        'total_spent',
        'total_orders',
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'trust_score' => 'decimal:2',
            'total_spent' => 'decimal:2',
            'total_orders' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (Customer $model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->name);
            }
        });
        static::updating(function (Customer $model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->name);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(CustomerDocument::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(RentalOrder::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function blacklistEntries(): HasMany
    {
        return $this->hasMany(BlacklistEntry::class);
    }

    public function trustScoreLogs(): HasMany
    {
        return $this->hasMany(TrustScoreLog::class);
    }

    public function damageReports(): HasMany
    {
        return $this->hasMany(DamageReport::class);
    }

    public function investigationCases(): HasMany
    {
        return $this->hasMany(InvestigationCase::class);
    }

    public function voucherUsages(): HasMany
    {
        return $this->hasMany(VoucherUsage::class);
    }

    public function testimonials(): HasMany
    {
        return $this->hasMany(Testimonial::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeBlacklisted($query)
    {
        $customerIds = BlacklistEntry::pluck('customer_id');
        return $query->whereIn('id', $customerIds);
    }

    public function isBlacklisted(): bool
    {
        return $this->blacklistEntries()->where('is_active', true)->exists();
    }

    public function hasValidDocuments(): bool
    {
        return $this->documents()
            ->where('status', 'verified')
            ->where('expiry_date', '>=', now())
            ->exists();
    }
}

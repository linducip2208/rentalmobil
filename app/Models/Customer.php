<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'customer_type',
        'corporate_account_id',
        'email',
        'phone',
        'address',
        'city',
        'province',
        'postal_code',
        'ktp_number',
        'sim_number',
        'npwp',
        'company_name',
        'company_address',
        'company_npwp',
        'date_of_birth',
        'gender',
        'ktp_expiry_date',
        'sim_expiry_date',
        'emergency_contact_name',
        'emergency_contact_phone',
        'trust_score',
        'total_spent',
        'total_orders',
        'loyalty_tier',
        'loyalty_points',
        'verification_status',
        'notes',
        'is_active',
        'password',
        'last_login_at',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'ktp_expiry_date' => 'date',
        'sim_expiry_date' => 'date',
        'trust_score' => 'integer',
        'total_spent' => 'decimal:2',
        'total_orders' => 'integer',
        'loyalty_points' => 'integer',
            'is_active' => 'boolean',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
    ];

    public function corporateAccount(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(CorporateAccount::class);
    }

    // Relationships
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    protected $hidden = ['password', 'remember_token'];

    public function rentalOrders()
    {
        return $this->hasMany(RentalOrder::class);
    }

    public function loyaltyLedgers()
    {
        return $this->hasMany(LoyaltyLedger::class);
    }

    public function referralsMade()
    {
        return $this->hasMany(Referral::class, 'referrer_customer_id');
    }

    public function referralsReceived()
    {
        return $this->hasOne(Referral::class, 'referred_customer_id');
    }

    public function faceVerifications()
    {
        return $this->hasMany(FaceVerification::class);
    }

    public function fraudHits()
    {
        return $this->hasMany(FraudHit::class);
    }

    public function documents()
    {
        return $this->hasMany(CustomerDocument::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function deposits()
    {
        return $this->hasMany(Deposit::class);
    }

    public function trustScoreLogs()
    {
        return $this->hasMany(TrustScoreLog::class);
    }

    public function blacklistEntries()
    {
        return $this->hasMany(BlacklistEntry::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeVerified($query)
    {
        return $query->where('verification_status', 'verified');
    }

    public function scopeIndividual($query)
    {
        return $query->where('customer_type', 'individual');
    }

    public function scopeCorporate($query)
    {
        return $query->where('customer_type', 'corporate');
    }
}

<?php

namespace App\Models;

use App\Models\Concerns\BelongsToLocation;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Vehicle extends Model
{
    use BelongsToLocation, HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'brand_id',
        'location_id',
        'name',
        'slug',
        'description',
        'plate_number',
        'year',
        'color',
        'mileage',
        'fuel_type',
        'transmission',
        'seat_count',
        'engine_cc',
        'daily_rate',
        'weekly_rate',
        'monthly_rate',
        'late_fee_per_hour',
        'late_fee_per_day',
        'deposit_amount',
        'purchase_price',
        'residual_value',
        'useful_life_months',
        'depreciation_method',
        'acquired_at',
        'depreciation_start_date', 'accumulated_depreciation',
        'asset_account_id', 'accumulated_depreciation_account_id', 'depreciation_expense_account_id',
        'status',
        'features',
        'photo_url',
        'is_active',
        'is_insured',
        'last_serviced_at',
        'last_km_at',
        'stnk_due_date',
        'tax_due_date',
        'tax_5y_due_date',
        'kir_due_date',
        'created_by',
    ];

    protected $casts = [
        'year' => 'integer',
        'mileage' => 'integer',
        'seat_count' => 'integer',
        'engine_cc' => 'integer',
        'daily_rate' => 'decimal:2',
        'weekly_rate' => 'decimal:2',
        'monthly_rate' => 'decimal:2',
        'late_fee_per_hour' => 'decimal:2',
        'late_fee_per_day' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
        'purchase_price' => 'decimal:2',
        'residual_value' => 'decimal:2',
        'useful_life_months' => 'integer',
        'acquired_at' => 'date',
        'depreciation_start_date' => 'date',
        'accumulated_depreciation' => 'decimal:2',
        'stnk_due_date' => 'date',
        'tax_due_date' => 'date',
        'tax_5y_due_date' => 'date',
        'kir_due_date' => 'date',
        'features' => 'array',
        'is_active' => 'boolean',
        'is_insured' => 'boolean',
        'last_serviced_at' => 'datetime',
        'last_km_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($vehicle) {
            if (empty($vehicle->slug)) {
                $vehicle->slug = Str::slug($vehicle->name.'-'.$vehicle->plate_number);
            }
        });
    }

    // Relationships
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function photos()
    {
        return $this->hasMany(VehiclePhoto::class)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Media priority: real uploaded cover > real gallery photo > legacy
     * photo_url > generated demo artwork. Real photos always outrank demo
     * media once an admin uploads them.
     */
    public function coverPhotoUrl(): ?string
    {
        $photos = $this->photos;

        $real = $photos->firstWhere('is_primary', true)
            ?? $photos->first(fn (VehiclePhoto $p) => ! $p->isDemoMedia());
        $demo = $photos->firstWhere('is_primary', true);
        $demo ??= $photos->first(fn (VehiclePhoto $p) => $p->isDemoMedia());

        foreach ([$real, $demo] as $photo) {
            if ($photo && filled($photo->photo_url)) {
                return $photo->url;
            }
        }

        if (filled($this->photo_url)) {
            return str_starts_with((string) $this->photo_url, 'http')
                ? $this->photo_url
                : Storage::disk('public')->url($this->photo_url);
        }

        return null;
    }

    /**
     * Gallery photos excluding the cover and demo fallbacks once real media
     * exists.
     */
    public function galleryPhotos(): Collection
    {
        $photos = $this->photos;
        $hasRealMedia = $photos->contains(fn (VehiclePhoto $p) => ! $p->isDemoMedia());

        return $photos->reject(
            fn (VehiclePhoto $photo) => ($photo->is_primary && $photo->is($photos->first()))
                || ($hasRealMedia && $photo->isDemoMedia())
        )->values();
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function rentalOrders()
    {
        return $this->hasMany(RentalOrder::class);
    }

    public function contracts()
    {
        return $this->hasMany(Contract::class);
    }

    public function maintenanceLogs()
    {
        return $this->hasMany(MaintenanceLog::class);
    }

    public function serviceSchedules()
    {
        return $this->hasMany(ServiceSchedule::class);
    }

    public function fuelLogs()
    {
        return $this->hasMany(FuelLog::class);
    }

    public function kmLogs()
    {
        return $this->hasMany(KmLog::class);
    }

    public function gpsLogs()
    {
        return $this->hasMany(GpsLog::class);
    }

    public function deliveries()
    {
        return $this->hasMany(Delivery::class);
    }

    public function transfers()
    {
        return $this->hasMany(Transfer::class);
    }

    public function insurancePolicies()
    {
        return $this->hasMany(InsurancePolicy::class);
    }

    public function damageReports()
    {
        return $this->hasMany(DamageReport::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function holds()
    {
        return $this->hasMany(BookingHold::class);
    }

    public function investments()
    {
        return $this->hasMany(VehicleInvestment::class);
    }

    /**
     * Depresiasi garis lurus per bulan dari purchase_price & useful_life_months.
     */
    public function monthlyDepreciation(): float
    {
        if (! $this->purchase_price || ! $this->useful_life_months) {
            return 0.0;
        }

        return round(((float) $this->purchase_price - (float) $this->residual_value) / (int) $this->useful_life_months, 2);
    }

    // Scopes
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available')->where('is_active', true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRented($query)
    {
        return $query->where('status', 'rented');
    }

    public function scopeMaintenance($query)
    {
        return $query->where('status', 'maintenance');
    }

    /**
     * Dokumen kendaraan (STNK/pajak/KIR) yang kedaluwarsa sebelum tanggal tertentu.
     * Dipakai untuk blok booking + reminder H-30/H-7.
     */
    public function expiredDocuments(?CarbonInterface $until = null): array
    {
        $until ??= now();
        $docs = [
            'stnk_due_date' => 'STNK',
            'tax_due_date' => 'Pajak Tahunan',
            'tax_5y_due_date' => 'Pajak 5 Tahunan',
            'kir_due_date' => 'KIR',
        ];
        $expired = [];

        foreach ($docs as $field => $label) {
            if ($this->{$field} && $this->{$field}->lt($until)) {
                $expired[] = "{$label} ({$this->{$field}->format('d/m/Y')})";
            }
        }

        return $expired;
    }

    public function hasValidDocumentsUntil(Carbon $date): bool
    {
        return $this->expiredDocuments($date->copy()->endOfDay()) === [];
    }
}

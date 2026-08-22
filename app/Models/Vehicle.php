<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Models\Concerns\BelongsToLocation;

class Vehicle extends Model
{
    use HasFactory, SoftDeletes, BelongsToLocation;

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
                $vehicle->slug = Str::slug($vehicle->name . '-' . $vehicle->plate_number);
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
        return $this->hasMany(VehiclePhoto::class);
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
    public function expiredDocuments(?\Carbon\CarbonInterface $until = null): array
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

    public function hasValidDocumentsUntil(\Illuminate\Support\Carbon $date): bool
    {
        return $this->expiredDocuments($date->copy()->endOfDay()) === [];
    }
}

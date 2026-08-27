<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class InsuranceClaim extends Model
{
    protected $fillable = [
        'claim_number',
        'insurance_policy_id',
        'damage_report_id',
        'police_report_id',
        'incident_date',
        'filed_amount',
        'approved_amount',
        'status',
        'documents',
        'submitted_at',
        'decided_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'incident_date' => 'date',
            'filed_amount' => 'decimal:2',
            'approved_amount' => 'decimal:2',
            'documents' => 'array',
            'submitted_at' => 'datetime',
            'decided_at' => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (self $claim) {
            if (blank($claim->claim_number)) {
                $claim->claim_number = 'CLM-'.now()->format('ymd').'-'.strtoupper(Str::random(5));
            }
        });
    }

    public function insurancePolicy(): BelongsTo
    {
        return $this->belongsTo(InsurancePolicy::class);
    }

    public function damageReport(): BelongsTo
    {
        return $this->belongsTo(DamageReport::class);
    }

    public function policeReport(): BelongsTo
    {
        return $this->belongsTo(PoliceReport::class);
    }
}

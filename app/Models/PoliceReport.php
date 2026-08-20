<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PoliceReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'investigation_case_id',
        'vehicle_id',
        'rental_order_id',
        'report_number',
        'police_station',
        'officer_name',
        'officer_badge',
        'report_date',
        'incident_date',
        'incident_location',
        'description',
        'status',
        'document_path',
        'follow_up_date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'report_date' => 'date',
            'incident_date' => 'datetime',
            'follow_up_date' => 'date',
        ];
    }

    public function investigationCase(): BelongsTo
    {
        return $this->belongsTo(InvestigationCase::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function rentalOrder(): BelongsTo
    {
        return $this->belongsTo(RentalOrder::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeFiled($query)
    {
        return $query->where('status', 'filed');
    }

    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }
}

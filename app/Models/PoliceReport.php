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
        'report_date',
        'report_text',
        'status',
        'documents',
    ];

    protected function casts(): array
    {
        return [
            'report_date' => 'date',
            'documents' => 'array',
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

    public function scopeFiled($query)
    {
        return $query->where('status', 'filed');
    }

    public function scopeInvestigating($query)
    {
        return $query->where('status', 'investigating');
    }

    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    public function scopeNoAction($query)
    {
        return $query->where('status', 'no_action');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InvestigationCase extends Model
{
    use HasFactory;

    protected $fillable = [
        'case_number',
        'vehicle_id',
        'customer_id',
        'rental_order_id',
        'assigned_to',
        'priority',
        'status',
        'title',
        'description',
        'evidence',
        'resolution',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'evidence' => 'array',
            'resolved_at' => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (InvestigationCase $model) {
            if (empty($model->case_number)) {
                $model->case_number = static::generateCaseNumber();
            }
        });
    }

    public static function generateCaseNumber(): string
    {
        $prefix = 'INV';
        $date = now()->format('ymd');
        $last = static::where('case_number', 'like', "{$prefix}{$date}%")
            ->orderByDesc('case_number')
            ->value('case_number');

        if ($last) {
            $sequence = (int) substr($last, -4) + 1;
        } else {
            $sequence = 1;
        }

        return $prefix . $date . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function rentalOrder(): BelongsTo
    {
        return $this->belongsTo(RentalOrder::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function policeReports(): HasMany
    {
        return $this->hasMany(PoliceReport::class);
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    public function scopeHighPriority($query)
    {
        return $query->where('priority', 'high');
    }

    public function scopeCritical($query)
    {
        return $query->where('priority', 'critical');
    }
}

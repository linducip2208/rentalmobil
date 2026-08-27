<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'transfer_number',
        'from_location_id',
        'to_location_id',
        'vehicle_id',
        'scheduled_date',
        'completed_date',
        'status',
        'reason',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_date' => 'date',
            'completed_date' => 'date',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (Transfer $model) {
            if (empty($model->transfer_number)) {
                $model->transfer_number = static::generateTransferNumber();
            }
        });
    }

    public static function generateTransferNumber(): string
    {
        $prefix = 'TRF';
        $date = now()->format('ymd');
        $last = static::where('transfer_number', 'like', "{$prefix}{$date}%")
            ->orderByDesc('transfer_number')
            ->value('transfer_number');

        if ($last) {
            $sequence = (int) substr($last, -4) + 1;
        } else {
            $sequence = 1;
        }

        return $prefix.$date.str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    public function fromLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'from_location_id');
    }

    public function toLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'to_location_id');
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeInTransit($query)
    {
        return $query->where('status', 'in_transit');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }
}

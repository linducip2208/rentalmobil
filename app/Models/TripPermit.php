<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TripPermit extends Model
{
    protected $fillable = [
        'spj_number',
        'rental_order_id',
        'driver_id',
        'route_from',
        'route_to',
        'fuel_start_level',
        'fuel_end_level',
        'odometer_start',
        'odometer_end',
        'toll_cost',
        'parking_cost',
        'accommodation_cost',
        'notes',
        'status',
        'started_at',
        'finished_at',
    ];

    protected function casts(): array
    {
        return [
            'toll_cost' => 'decimal:2',
            'parking_cost' => 'decimal:2',
            'accommodation_cost' => 'decimal:2',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (TripPermit $model) {
            if (empty($model->spj_number)) {
                $model->spj_number = static::generateSpjNumber();
            }
        });
    }

    public static function generateSpjNumber(): string
    {
        $prefix = 'SPJ-'.now()->format('Ymd').'-';
        $last = static::query()->where('spj_number', 'like', "{$prefix}%")->orderByDesc('spj_number')->value('spj_number');

        $sequence = $last ? (int) substr($last, -4) + 1 : 1;

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }

    public function rentalOrder(): BelongsTo
    {
        return $this->belongsTo(RentalOrder::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    /** Total biaya operasional yang bisa ditagih ke customer. */
    public function totalOperationalCost(): float
    {
        return (float) $this->toll_cost + (float) $this->parking_cost + (float) $this->accommodation_cost;
    }

    public static function fuelLevels(): array
    {
        return ['full' => 'Penuh', 'three_quarter' => '3/4', 'half' => '1/2', 'quarter' => '1/4', 'empty' => 'Kosong'];
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class SeasonPeriod extends Model
{
    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'multiplier',
        'is_recurring_annual',
        'location_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'multiplier' => 'decimal:2',
            'is_recurring_annual' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /** Rule yang mencakup tanggal tertentu (bulan/tanggal saja jika recurring). */
    public static function forDate(string $date, ?int $locationId = null): ?self
    {
        $day = Carbon::parse($date);
        $md = (int) $day->format('md');

        $candidates = static::query()->active()
            ->when($locationId, fn ($q) => $q->where(fn ($qq) => $qq->whereNull('location_id')->orWhere('location_id', $locationId)))
            ->orderByDesc('multiplier')
            ->get();

        foreach ($candidates as $rule) {
            if (! $rule->is_recurring_annual) {
                if ($day->between($rule->start_date, $rule->end_date)) {
                    return $rule;
                }

                continue;
            }

            // Recurring tahunan: bandingkan (bulan+tanggal), dukung rentang lintas tahun.
            $startMd = (int) $rule->start_date->format('md');
            $endMd = (int) $rule->end_date->format('md');

            if ($startMd <= $endMd ? ($md >= $startMd && $md <= $endMd) : ($md >= $startMd || $md <= $endMd)) {
                return $rule;
            }
        }

        return null;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}

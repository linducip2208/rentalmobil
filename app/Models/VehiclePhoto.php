<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class VehiclePhoto extends Model
{
    protected $fillable = [
        'vehicle_id',
        'photo_url',
        'caption',
        'alt_text',
        'type',
        'disk',
        'sort_order',
        'is_primary',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_primary' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        // Invariant: at most ONE primary (cover) photo per vehicle.
        static::saving(function (self $photo) {
            if ($photo->is_primary && $photo->vehicle_id) {
                self::where('vehicle_id', $photo->vehicle_id)
                    ->where('id', '!=', $photo->id)
                    ->update(['is_primary' => false]);
            }
        });

        // When the cover photo is deleted, promote the next photo by sort order.
        static::deleted(function (self $photo) {
            if (! $photo->is_primary || ! $photo->vehicle_id) {
                return;
            }

            $next = self::where('vehicle_id', $photo->vehicle_id)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->first();

            $next?->update(['is_primary' => true]);
        });
    }

    /**
     * Demote every sibling then promote this photo — used by admin flows that
     * swap the cover explicitly. Safe inside an outer transaction.
     */
    public function markAsPrimary(): self
    {
        DB::transaction(function () {
            self::where('vehicle_id', $this->vehicle_id)
                ->where('id', '!=', $this->id)
                ->update(['is_primary' => false]);

            $this->update(['is_primary' => true]);
        });

        return $this;
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Resolved public URL for the photo, whether it is a storage path
     * or an absolute URL.
     */
    public function getUrlAttribute(): string
    {
        $path = (string) $this->photo_url;

        if ($path === '') {
            return '';
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return Storage::disk($this->disk ?: 'public')->url($path);
    }

    /**
     * True for generated demo artwork (storage path under vehicles/demo).
     * Real uploaded media always takes precedence over demo fallbacks.
     */
    public function isDemoMedia(): bool
    {
        return str_starts_with((string) $this->photo_url, 'vehicles/demo/');
    }

    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    public function scopeRealMedia($query)
    {
        return $query->whereNotLike('photo_url', 'vehicles/demo/%');
    }

    public function scopeSorted($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}

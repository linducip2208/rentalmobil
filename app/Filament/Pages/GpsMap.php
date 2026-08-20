<?php

namespace App\Filament\Pages;

use App\Models\GpsTracker;
use Filament\Pages\Page;
use Illuminate\Http\JsonResponse;

class GpsMap extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-map';

    protected static \UnitEnum|string|null $navigationGroup = '🔧 Operasional';

    protected static int|null $navigationSort = 9;

    protected static string|null $navigationLabel = 'GPS Map';

    protected static string|null $title = 'GPS Tracking Map';

    protected string $view = 'filament.pages.gps-map';

    public function getTrackersJson(): JsonResponse
    {
        $trackers = GpsTracker::with('vehicle')
            ->where('is_active', true)
            ->whereNotNull('last_latitude')
            ->get()
            ->map(fn(GpsTracker $t) => [
                'id' => $t->id,
                'device_name' => $t->device_name ?? $t->device_id,
                'vehicle' => $t->vehicle?->name ?? 'Unassigned',
                'plate' => $t->vehicle?->plate_number ?? '-',
                'lat' => (float) $t->last_latitude,
                'lng' => (float) $t->last_longitude,
                'speed' => (float) ($t->last_speed ?? 0),
                'heading' => (int) ($t->last_heading ?? 0),
                'battery' => $t->last_battery_level,
                'is_online' => $t->isOnline(),
                'last_update' => $t->last_update_at?->diffForHumans() ?? 'N/A',
            ]);

        return response()->json(['trackers' => $trackers]);
    }
}

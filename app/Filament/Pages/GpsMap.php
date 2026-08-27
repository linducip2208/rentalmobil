<?php

namespace App\Filament\Pages;

use App\Models\GpsTracker;
use App\Services\Gps\RouteReplayService;
use Filament\Pages\Page;
use Illuminate\Http\JsonResponse;
use Livewire\Attributes\Url;

class GpsMap extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-map';

    protected static \UnitEnum|string|null $navigationGroup = 'GPS & Monitoring';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Peta Armada';

    protected static ?string $title = 'GPS Tracking Map';

    protected string $view = 'filament.pages.gps-map';

    #[Url]
    public ?int $replayTrackerId = null;

    public string $replayFrom;

    public string $replayTo;

    public function mount(): void
    {
        $this->replayFrom = now()->toDateString();
        $this->replayTo = now()->toDateString();
    }

    public function getTrackersJson(): JsonResponse
    {
        $trackers = GpsTracker::with('vehicle')
            ->where('is_active', true)
            ->whereNotNull('last_latitude')
            ->get()
            ->map(fn (GpsTracker $t) => [
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

    /**
     * Route replay: polyline + stops + heatmap dari gps_logs periode terpilih.
     */
    public function getReplayJson(): JsonResponse
    {
        $this->validate([
            'replayFrom' => 'required|date',
            'replayTo' => 'required|date|after_or_equal:replayFrom',
        ]);

        abort_if(! $this->replayTrackerId, 422, 'Pilih tracker terlebih dahulu.');

        $tracker = GpsTracker::findOrFail($this->replayTrackerId);
        $replay = app(RouteReplayService::class)->replay($tracker, $this->replayFrom, $this->replayTo);

        return response()->json([
            'tracker' => [
                'id' => $tracker->id,
                'vehicle' => $tracker->vehicle?->name ?? $tracker->device_name,
                'plate' => $tracker->vehicle?->plate_number,
            ],
            ...$replay,
        ]);
    }
}

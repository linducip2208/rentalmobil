<?php

namespace App\Services\Gps;

use App\Models\GpsIntegration;
use App\Models\GpsLog;
use App\Models\GpsTracker;
use Carbon\Carbon;

class GpsPositionIngestor
{
    public function __construct(private GpsAlertService $alerts) {}

    public function ingest(GpsIntegration $integration, array $record): ?GpsLog
    {
        $map = $integration->field_mapping ?? [];
        $externalId = $this->value($record, $map, 'device_id');
        if (blank($externalId)) throw new \RuntimeException('Mapping device_id tidak menghasilkan nilai.');

        $tracker = GpsTracker::where('gps_integration_id', $integration->id)
            ->where('external_device_id', (string) $externalId)->first();
        if (!$tracker?->vehicle_id) return null;

        $latitude = $this->value($record, $map, 'latitude');
        $longitude = $this->value($record, $map, 'longitude');
        if (!is_numeric($latitude) || !is_numeric($longitude)) throw new \RuntimeException('Latitude/longitude provider tidak valid.');

        $recordedAt = $this->value($record, $map, 'recorded_at');
        $recordedAt = filled($recordedAt) ? Carbon::parse($recordedAt) : now();
        $speed = $this->value($record, $map, 'speed');
        $heading = $this->value($record, $map, 'heading');
        $battery = $this->value($record, $map, 'battery_level');

        $externalEventId = $this->value($record, $map, 'event_id');
        $payloadHash = hash('sha256', json_encode($record, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $identity = filled($externalEventId) ? ['gps_tracker_id' => $tracker->id, 'external_event_id' => (string) $externalEventId] : ['gps_tracker_id' => $tracker->id, 'payload_hash' => $payloadHash];
        $log = GpsLog::firstOrCreate($identity, [
            'vehicle_id' => $tracker->vehicle_id, 'payload_hash' => $payloadHash, 'latitude' => $latitude, 'longitude' => $longitude,
            'speed' => is_numeric($speed) ? $speed : null, 'heading' => is_numeric($heading) ? $heading : null,
            'accuracy' => $this->numeric($this->value($record, $map, 'accuracy')),
            'battery_level' => $this->numeric($battery),
            'recorded_at' => $recordedAt,
        ]);

        if (!$log->wasRecentlyCreated) return null;
        $tracker->updateLocation((float) $latitude, (float) $longitude, $this->numeric($speed), $this->integer($heading), $this->integer($battery));
        $this->alerts->evaluate($tracker, ['latitude' => $latitude, 'longitude' => $longitude, 'speed' => $speed, 'battery_level' => $battery], $recordedAt);
        return $log;
    }

    private function value(array $record, array $map, string $field): mixed
    {
        $path = data_get($map, $field);
        return filled($path) ? data_get($record, $path) : null;
    }

    private function numeric(mixed $value): ?float { return is_numeric($value) ? (float) $value : null; }
    private function integer(mixed $value): ?int { return is_numeric($value) ? (int) $value : null; }
}

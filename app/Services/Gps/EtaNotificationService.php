<?php

namespace App\Services\Gps;

use App\Models\Delivery;
use App\Models\GpsTracker;
use App\Models\Location;
use App\Services\NotificationDispatcher;

/**
 * Notifikasi ETA: hitung estimasi waktu tiba dari posisi live tracker
 * ke lokasi tujuan delivery, lalu antre notifikasi pada ambang waktu.
 */
class EtaNotificationService
{
    public const NOTIFY_AT_MINUTES = [30, 10];

    public function processActiveDeliveries(): array
    {
        $results = ['checked' => 0, 'notified' => 0];

        $deliveries = Delivery::whereIn('status', ['scheduled', 'in_progress', 'dispatched'])
            ->whereDate('scheduled_date', '<=', now())
            ->with(['toLocation', 'rentalOrder.customer'])
            ->get();

        foreach ($deliveries as $delivery) {
            if (! $delivery->to_location_id) {
                continue;
            }

            $destination = Location::find($delivery->to_location_id);

            if (! $destination?->latitude || ! $destination?->longitude) {
                continue;
            }

            $tracker = GpsTracker::where('vehicle_id', $delivery->vehicle_id)
                ->where('is_active', true)
                ->whereNotNull('last_latitude')
                ->first();

            if (! $tracker) {
                continue;
            }

            $results['checked']++;

            $distanceKm = $this->haversineKm(
                (float) $tracker->last_latitude,
                (float) $tracker->last_longitude,
                (float) $destination->latitude,
                (float) $destination->longitude
            );

            $speedKmh = max(20.0, (float) ($tracker->last_speed ?? 30));
            $etaMinutes = (int) ceil(($distanceKm / $speedKmh) * 60);
            $eta = now()->addMinutes($etaMinutes);

            foreach (self::NOTIFY_AT_MINUTES as $threshold) {
                if ($etaMinutes <= $threshold && ! $this->alreadyNotified($delivery, $threshold)) {
                    app(NotificationDispatcher::class)->dispatch('delivery_eta', $delivery, [
                        'threshold_minutes' => $threshold,
                        'eta_at' => $eta->format('H:i'),
                        'distance_km' => round($distanceKm, 1),
                        'customer_name' => $delivery->rentalOrder?->customer?->name,
                        'address' => $delivery->to_address ?? $destination->name,
                    ]);
                    $this->markNotified($delivery, $threshold);
                    $results['notified']++;
                }
            }
        }

        return $results;
    }

    protected function alreadyNotified(Delivery $delivery, int $threshold): bool
    {
        return collect($delivery->notes ?? '')
            ->when(true, fn () => str_contains((string) $delivery->notes, "eta_{$threshold}_sent"));
    }

    protected function markNotified(Delivery $delivery, int $threshold): void
    {
        $delivery->update([
            'notes' => trim(($delivery->notes ? $delivery->notes."\n" : '')."[ETA] eta_{$threshold}_sent at ".now()->toDateTimeString()),
        ]);
    }

    protected function haversineKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;

        return round(6371.0 * 2 * asin(min(1.0, sqrt($a))), 2);
    }
}

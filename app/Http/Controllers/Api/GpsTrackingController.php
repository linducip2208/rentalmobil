<?php

namespace App\Http\Controllers\Api;

use App\Models\GpsLog;
use App\Models\GpsTracker;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\RateLimiter;

class GpsTrackingController extends Controller
{
    public function reportLocation(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'device_id' => 'required|string|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'speed' => 'nullable|numeric|min:0',
            'heading' => 'nullable|integer|between:0,360',
            'accuracy' => 'nullable|numeric|min:0',
            'battery_level' => 'nullable|integer|between:0,100',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $tracker = GpsTracker::where('device_id', $request->string('device_id'))
            ->where('is_active', true)
            ->first();

        if (!$tracker || !$tracker->acceptsToken($request->bearerToken())) {
            return response()->json(['message' => 'Kredensial tracker tidak valid.'], 401);
        }
        if (!$tracker->vehicle_id) {
            return response()->json(['message' => 'Tracker belum dipasangkan ke kendaraan.'], 409);
        }

        $rateKey = 'gps-ingest:'.$tracker->id;
        if (RateLimiter::tooManyAttempts($rateKey, 120)) {
            return response()->json(['message' => 'Terlalu banyak laporan lokasi.'], 429);
        }
        RateLimiter::hit($rateKey, 60);

        $gpsLog = GpsLog::create([
            'vehicle_id' => $tracker->vehicle_id,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'speed' => $request->speed ?? 0,
            'heading' => $request->heading ?? 0,
            'accuracy' => $request->accuracy ?? 0,
            'battery_level' => $request->battery_level ?? null,
            'recorded_at' => now(),
        ]);

        $tracker->updateLocation(
            (float) $request->latitude,
            (float) $request->longitude,
            $request->filled('speed') ? (float) $request->speed : null,
            $request->filled('heading') ? (int) $request->heading : null,
            $request->filled('battery_level') ? (int) $request->battery_level : null,
        );

        return response()->json([
            'success' => true,
            'gps_log_id' => $gpsLog->id,
        ]);
    }

    public function getActiveTrackers(): JsonResponse
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

    public function getActiveVehicles(): JsonResponse
    {
        $vehicles = Vehicle::query()
            ->with(['location', 'category', 'brand'])
            ->where('is_active', true)
            ->whereNotIn('status', ['retired'])
            ->get()
            ->map(function ($vehicle) {
                $latestGps = GpsLog::where('vehicle_id', $vehicle->id)
                    ->orderByDesc('recorded_at')
                    ->first();

                return [
                    'id' => $vehicle->id,
                    'name' => $vehicle->name,
                    'plate' => $vehicle->plate_number,
                    'status' => $vehicle->status,
                    'category' => $vehicle->category?->name,
                    'brand' => $vehicle->brand?->name,
                    'location' => $vehicle->location?->name,
                    'latitude' => $latestGps?->latitude,
                    'longitude' => $latestGps?->longitude,
                    'speed' => $latestGps?->speed ?? 0,
                    'heading' => $latestGps?->heading ?? 0,
                    'battery_level' => $latestGps?->battery_level,
                    'last_update' => $latestGps?->recorded_at?->toISOString(),
                ];
            });

        return response()->json(['vehicles' => $vehicles]);
    }
}

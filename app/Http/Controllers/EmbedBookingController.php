<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Location;
use App\Models\Vehicle;
use App\Services\PricingEngine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API ketersediaan + widget embeddable untuk dipasang di website mitra.
 * CORS dibuka untuk endpoint ini agar iframe/script lintas domain berfungsi.
 */
class EmbedBookingController extends Controller
{
    public function availability(Request $request, PricingEngine $pricing): JsonResponse
    {
        $d = $request->validate([
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
            'location_id' => 'nullable|exists:locations,id',
            'category_id' => 'nullable|exists:categories,id',
            'per_page' => 'nullable|integer|min:1|max:24',
        ]);

        $query = Vehicle::query()
            ->with(['category', 'brand'])
            ->where('is_active', true)
            ->where('status', 'available');

        if (!empty($d['location_id'])) {
            $query->where('location_id', $d['location_id']);
        }

        if (!empty($d['category_id'])) {
            $query->where('category_id', $d['category_id']);
        }

        $vehicles = $query->paginate($d['per_page'] ?? 8);

        $items = collect($vehicles->items())->map(function (Vehicle $vehicle) use ($pricing, $d) {
            $quote = $pricing->calculateRentalPrice(
                $vehicle,
                $d['start_date'],
                $d['end_date']
            );

            return [
                'id' => $vehicle->id,
                'name' => $vehicle->name,
                'plate' => $vehicle->plate_number,
                'category' => $vehicle->category?->name,
                'brand' => $vehicle->brand?->name,
                'photo_url' => $vehicle->photo_url,
                'seat_count' => $vehicle->seat_count,
                'transmission' => $vehicle->transmission,
                'daily_rate' => (float) $quote['effective_daily_rate'],
                'total' => (float) $quote['total'],
                'deposit' => (float) $quote['deposit'],
                'booking_url' => url('/booking?vehicle_id=' . $vehicle->id . '&start=' . $d['start_date'] . '&end=' . $d['end_date']),
            ];
        });

        return response()
            ->json([
                'data' => $items,
                'meta' => [
                    'current_page' => $vehicles->currentPage(),
                    'last_page' => $vehicles->lastPage(),
                    'total' => $vehicles->total(),
                    'currency' => 'IDR',
                ],
            ])
            ->header('Access-Control-Allow-Origin', '*');
    }

    public function meta(): JsonResponse
    {
        return response()
            ->json([
                'locations' => Location::active()->get(['id', 'name', 'city']),
                'categories' => Category::all(['id', 'name']),
            ])
            ->header('Access-Control-Allow-Origin', '*');
    }
}

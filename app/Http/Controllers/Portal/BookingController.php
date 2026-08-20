<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Addon;
use App\Models\Booking;
use App\Models\Driver;
use App\Models\Location;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index()
    {
        $customer = auth('customer')->user()->customer;

        $bookings = $customer->bookings()
            ->with('vehicle')
            ->latest()
            ->paginate(10);

        return view('portal.bookings.index', compact('bookings'));
    }

    public function create()
    {
        $vehicles = Vehicle::available()->with(['category', 'brand', 'location'])->get();
        $locations = Location::active()->get();
        $drivers = Driver::active()->get();
        $addons = Addon::active()->orderBy('sort_order')->get();

        return view('portal.bookings.create', compact('vehicles', 'locations', 'drivers', 'addons'));
    }

    public function store(Request $request)
    {
        $customer = auth('customer')->user()->customer;

        $validated = $request->validate([
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'pickup_location_id' => ['required', 'exists:locations,id'],
            'return_location_id' => ['nullable', 'exists:locations,id'],
            'rental_type' => ['required', 'in:self_drive,with_driver'],
            'driver_id' => ['required_if:rental_type,with_driver', 'nullable', 'exists:drivers,id'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'pickup_time' => ['nullable', 'date_format:H:i'],
            'return_time' => ['nullable', 'date_format:H:i'],
            'addons' => ['nullable', 'array'],
            'addons.*' => ['exists:addons,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $vehicle = Vehicle::findOrFail($validated['vehicle_id']);
        $start = now()->parse($validated['start_date']);
        $end = now()->parse($validated['end_date']);
        $days = max(1, $start->diffInDays($end));

        $dailyRate = (float) $vehicle->daily_rate;
        $subtotal = $dailyRate * $days;

        $addonTotal = 0;
        $addonItems = [];
        if (!empty($validated['addons'])) {
            $selectedAddons = Addon::whereIn('id', $validated['addons'])->get();
            foreach ($selectedAddons as $addon) {
                $price = match ($addon->price_type) {
                    'daily' => (float) $addon->price * $days,
                    default => (float) $addon->price,
                };
                $addonTotal += $price;
                $addonItems[] = $addon;
            }
        }

        $taxRate = 0.11;
        $taxAmount = ($subtotal + $addonTotal) * $taxRate;
        $totalAmount = $subtotal + $addonTotal + $taxAmount;

        $booking = Booking::create([
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'pickup_location_id' => $validated['pickup_location_id'],
            'return_location_id' => $validated['return_location_id'] ?? $validated['pickup_location_id'],
            'rental_type' => $validated['rental_type'],
            'driver_id' => $validated['driver_id'] ?? null,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'estimated_return_date' => $validated['end_date'],
            'pickup_time' => $validated['pickup_time'] ?? null,
            'return_time' => $validated['return_time'] ?? null,
            'subtotal' => $subtotal,
            'discount_amount' => 0,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'deposit_amount' => $vehicle->deposit_amount,
            'status' => 'pending',
            'notes' => $validated['notes'] ?? null,
            'created_by' => auth('customer')->id(),
        ]);

        foreach ($addonItems as $addon) {
            $price = match ($addon->price_type) {
                'daily' => (float) $addon->price * $days,
                default => (float) $addon->price,
            };
            $booking->rentalOrder()->create([
                'customer_id' => $customer->id,
                'vehicle_id' => $vehicle->id,
                'location_id' => $validated['pickup_location_id'],
                'rental_type' => $validated['rental_type'],
                'driver_id' => $validated['driver_id'] ?? null,
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'duration_days' => $days,
                'daily_rate' => $dailyRate,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'deposit_amount' => $vehicle->deposit_amount,
                'status' => 'pending',
                'created_by' => auth('customer')->id(),
            ]);
            break;
        }

        return redirect()->route('portal.bookings.show', $booking)
            ->with('success', 'Booking berhasil dibuat!');
    }

    public function show(Booking $booking)
    {
        $customer = auth('customer')->user()->customer;

        abort_if($booking->customer_id !== $customer->id, 403);

        $booking->load(['vehicle.brand', 'vehicle.category', 'pickupLocation', 'returnLocation', 'driver']);

        return view('portal.bookings.show', compact('booking'));
    }
}

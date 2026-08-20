<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\RentalOrder;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AvailabilityEngine
{
    /**
     * Check if a vehicle is available for a given date range.
     */
    public function checkAvailability(
        Vehicle $vehicle,
        string $startDate,
        string $endDate,
        ?int $excludeBookingId = null
    ): array {
        if (!$vehicle->is_active) {
            return [
                'available' => false,
                'conflicts' => collect(),
                'message' => 'Kendaraan tidak aktif.',
            ];
        }

        if ($vehicle->status !== 'available') {
            return [
                'available' => false,
                'conflicts' => collect(),
                'message' => 'Kendaraan sedang tidak tersedia (status: ' . $vehicle->status . ').',
            ];
        }

        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        if ($end->lt($start)) {
            return [
                'available' => false,
                'conflicts' => collect(),
                'message' => 'Tanggal akhir tidak boleh sebelum tanggal awal.',
            ];
        }

        $conflicts = $this->getConflictingRecords($vehicle->id, $start, $end, $excludeBookingId);

        return [
            'available' => $conflicts->isEmpty(),
            'conflicts' => $conflicts,
            'message' => $conflicts->isEmpty()
                ? 'Kendaraan tersedia untuk periode ini.'
                : 'Terdapat ' . $conflicts->count() . ' konflik pemesanan.',
        ];
    }

    /**
     * Find all available vehicles matching the given criteria.
     */
    public function findAvailableVehicles(
        string $startDate,
        string $endDate,
        ?int $categoryId = null,
        ?int $locationId = null,
        ?string $transmission = null,
        ?int $minSeats = null
    ): Collection {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        $query = Vehicle::query()
            ->where('is_active', true)
            ->where('status', 'available');

        if ($categoryId !== null) {
            $query->where('category_id', $categoryId);
        }
        if ($locationId !== null) {
            $query->where('location_id', $locationId);
        }
        if ($transmission !== null) {
            $query->where('transmission', $transmission);
        }
        if ($minSeats !== null) {
            $query->where('seat_count', '>=', $minSeats);
        }

        $busyBookingIds = Booking::query()
            ->where('status', '!=', 'cancelled')
            ->where('start_date', '<=', $end)
            ->where('end_date', '>=', $start)
            ->pluck('vehicle_id')
            ->toArray();

        $busyOrderIds = RentalOrder::query()
            ->whereNotIn('status', ['completed', 'cancelled', 'disputed'])
            ->where('start_date', '<=', $end)
            ->where('end_date', '>=', $start)
            ->pluck('vehicle_id')
            ->toArray();

        $busyIds = array_unique(array_merge($busyBookingIds, $busyOrderIds));

        if (!empty($busyIds)) {
            $query->whereNotIn('id', $busyIds);
        }

        return $query->with(['category', 'brand', 'location'])->get();
    }

    /**
     * Check if a date range overlaps with existing bookings or rental orders.
     */
    public function checkOverlap(
        string $startDate,
        string $endDate,
        $vehicleId,
        ?int $excludeBookingId = null
    ): bool {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        $bookingQuery = Booking::query()
            ->where('vehicle_id', $vehicleId)
            ->whereIn('status', ['confirmed', 'active', 'hold'])
            ->where('start_date', '<=', $end)
            ->where('end_date', '>=', $start);

        if ($excludeBookingId !== null) {
            $bookingQuery->where('id', '!=', $excludeBookingId);
        }

        if ($bookingQuery->exists()) {
            return true;
        }

        $orderQuery = RentalOrder::query()
            ->where('vehicle_id', $vehicleId)
            ->whereNotIn('status', ['completed', 'cancelled', 'disputed'])
            ->where('start_date', '<=', $end)
            ->where('end_date', '>=', $start);

        return $orderQuery->exists();
    }

    /**
     * Create a hold/quote reservation for a vehicle.
     */
    public function holdVehicle(
        Vehicle $vehicle,
        ?int $customerId,
        string $startDate,
        string $endDate,
        int $holdMinutes = 30
    ): Booking {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $durationDays = max(1, $start->diffInDays($end));

        return Booking::create([
            'customer_id' => $customerId,
            'vehicle_id' => $vehicle->id,
            'pickup_location_id' => $vehicle->location_id,
            'return_location_id' => $vehicle->location_id,
            'start_date' => $start,
            'end_date' => $end,
            'estimated_return_date' => $end,
            'rental_type' => 'self_drive',
            'duration_days' => $durationDays,
            'daily_rate_snapshot' => $vehicle->daily_rate,
            'subtotal' => round((float) $vehicle->daily_rate * $durationDays, 2),
            'total_amount' => round((float) $vehicle->daily_rate * $durationDays, 2),
            'deposit_amount' => $vehicle->deposit_amount,
            'status' => 'hold',
            'hold_expires_at' => now()->addMinutes($holdMinutes),
            'source' => 'system',
        ]);
    }

    /**
     * Release all holds that have passed their hold_expires_at.
     */
    public function releaseExpiredHolds(): int
    {
        return Booking::query()
            ->where('status', 'hold')
            ->where('hold_expires_at', '<', now())
            ->update(['status' => 'expired']);
    }

    /**
     * Get the availability calendar for a vehicle in a given month.
     *
     * @param  string  $month  Format: Y-m (e.g. '2026-08')
     */
    public function getVehicleCalendar(Vehicle $vehicle, string $month): array
    {
        $startDate = Carbon::parse($month . '-01')->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $orders = RentalOrder::where('vehicle_id', $vehicle->id)
            ->whereNotIn('status', ['completed', 'cancelled', 'disputed'])
            ->where('start_date', '<=', $endDate)
            ->where('end_date', '>=', $startDate)
            ->get(['id', 'start_date', 'end_date', 'order_number', 'status']);

        $bookings = Booking::where('vehicle_id', $vehicle->id)
            ->whereIn('status', ['confirmed', 'active', 'hold'])
            ->where('start_date', '<=', $endDate)
            ->where('end_date', '>=', $startDate)
            ->get(['id', 'start_date', 'end_date', 'booking_number', 'status']);

        $calendar = [];
        $current = $startDate->copy();

        while ($current->lte($endDate)) {
            $dateString = $current->toDateString();
            $reservation = null;

            foreach ($orders as $order) {
                if ($current->gte($order->start_date) && $current->lte($order->end_date)) {
                    $reservation = [
                        'type' => 'order',
                        'reference' => $order->order_number,
                        'status' => $order->status,
                    ];
                    break;
                }
            }

            if ($reservation === null) {
                foreach ($bookings as $booking) {
                    if ($current->gte($booking->start_date) && $current->lte($booking->end_date)) {
                        $reservation = [
                            'type' => 'booking',
                            'reference' => $booking->booking_number,
                            'status' => $booking->status,
                        ];
                        break;
                    }
                }
            }

            $calendar[] = [
                'date' => $dateString,
                'day' => $current->day,
                'day_of_week' => $current->dayOfWeekIso,
                'is_weekend' => in_array($current->dayOfWeekIso, [6, 7]),
                'is_available' => $reservation === null,
                'reservation' => $reservation,
            ];

            $current->addDay();
        }

        $totalDays = count($calendar);
        $availableDays = count(array_filter($calendar, fn($day) => $day['is_available']));
        $occupiedDays = $totalDays - $availableDays;

        return [
            'vehicle_id' => $vehicle->id,
            'vehicle_name' => $vehicle->name,
            'month' => $startDate->format('Y-m'),
            'total_days' => $totalDays,
            'available_days' => $availableDays,
            'occupied_days' => $occupiedDays,
            'occupancy_rate' => $totalDays > 0 ? round(($occupiedDays / $totalDays) * 100, 1) : 0,
            'daily_rate' => (float) $vehicle->daily_rate,
            'calendar' => $calendar,
        ];
    }

    /**
     * Get all conflicting bookings and rental orders for a vehicle and date range.
     */
    private function getConflictingRecords(
        int $vehicleId,
        Carbon $start,
        Carbon $end,
        ?int $excludeBookingId = null
    ): Collection {
        $conflicts = collect();

        $bookingQuery = Booking::query()
            ->where('vehicle_id', $vehicleId)
            ->whereIn('status', ['confirmed', 'active', 'hold'])
            ->where('start_date', '<=', $end)
            ->where('end_date', '>=', $start);

        if ($excludeBookingId !== null) {
            $bookingQuery->where('id', '!=', $excludeBookingId);
        }

        $conflictingBookings = $bookingQuery->get([
            'id', 'booking_number', 'start_date', 'end_date', 'status',
        ]);

        foreach ($conflictingBookings as $booking) {
            $conflicts->push([
                'type' => 'booking',
                'id' => $booking->id,
                'reference' => $booking->booking_number,
                'start_date' => $booking->start_date->toDateString(),
                'end_date' => $booking->end_date->toDateString(),
                'status' => $booking->status,
            ]);
        }

        $conflictingOrders = RentalOrder::query()
            ->where('vehicle_id', $vehicleId)
            ->whereNotIn('status', ['completed', 'cancelled', 'disputed'])
            ->where('start_date', '<=', $end)
            ->where('end_date', '>=', $start)
            ->get([
                'id', 'order_number', 'start_date', 'end_date', 'status',
            ]);

        foreach ($conflictingOrders as $order) {
            $conflicts->push([
                'type' => 'rental_order',
                'id' => $order->id,
                'reference' => $order->order_number,
                'start_date' => $order->start_date->toDateString(),
                'end_date' => $order->end_date->toDateString(),
                'status' => $order->status,
            ]);
        }

        return $conflicts;
    }
}

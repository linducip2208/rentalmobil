<?php

namespace App\Services;

use App\Models\RentalOrder;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AvailabilityService
{
    public function getAvailableVehicles(
        ?int $locationId = null,
        ?Carbon $startDate = null,
        ?Carbon $endDate = null,
        ?int $categoryId = null
    ): Collection {
        $startDate = $startDate ?? now();
        $endDate = $endDate ?? now()->addDay();

        $query = Vehicle::query()
            ->where('is_active', true)
            ->where('status', 'available');

        if ($locationId) {
            $query->where('location_id', $locationId);
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        $availableVehicleIds = $this->getAvailableVehicleIdsForPeriod($startDate, $endDate);

        $query->whereIn('id', $availableVehicleIds);

        return $query->with(['category', 'brand', 'location'])->get();
    }

    public function isVehicleAvailable(int $vehicleId, Carbon $startDate, Carbon $endDate): bool
    {
        $vehicle = Vehicle::find($vehicleId);

        if (!$vehicle || !$vehicle->is_active || !$vehicle->isAvailable()) {
            return false;
        }

        return $this->checkNoOverlappingReservations($vehicleId, $startDate, $endDate);
    }

    public function getVehicleCalendar(int $vehicleId, int $month, int $year): array
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $orders = RentalOrder::where('vehicle_id', $vehicleId)
            ->whereIn('status', ['confirmed', 'active'])
            ->where('start_date', '<=', $endDate)
            ->where('end_date', '>=', $startDate)
            ->get(['start_date', 'end_date', 'order_number', 'status']);

        $bookings = \App\Models\Booking::where('vehicle_id', $vehicleId)
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('start_date', '<=', $endDate)
            ->where('end_date', '>=', $startDate)
            ->get(['start_date', 'end_date', 'booking_number', 'status']);

        $calendar = [];
        $current = $startDate->copy();

        while ($current->lte($endDate)) {
            $dateString = $current->toDateString();
            $isOccupied = false;
            $reservationInfo = null;

            foreach ($orders as $order) {
                if ($current->gte($order->start_date) && $current->lte($order->end_date)) {
                    $isOccupied = true;
                    $reservationInfo = [
                        'type' => 'order',
                        'reference' => $order->order_number,
                        'status' => $order->status,
                    ];
                    break;
                }
            }

            if (!$isOccupied) {
                foreach ($bookings as $booking) {
                    if ($current->gte($booking->start_date) && $current->lte($booking->end_date)) {
                        $isOccupied = true;
                        $reservationInfo = [
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
                'is_available' => !$isOccupied,
                'reservation' => $reservationInfo,
            ];

            $current->addDay();
        }

        $totalDays = count($calendar);
        $availableDays = collect($calendar)->where('is_available', true)->count();
        $occupiedDays = $totalDays - $availableDays;
        $occupancyRate = $totalDays > 0 ? round(($occupiedDays / $totalDays) * 100, 1) : 0;

        return [
            'vehicle_id' => $vehicleId,
            'month' => $month,
            'year' => $year,
            'total_days' => $totalDays,
            'available_days' => $availableDays,
            'occupied_days' => $occupiedDays,
            'occupancy_rate' => $occupancyRate,
            'calendar' => $calendar,
        ];
    }

    protected function getAvailableVehicleIdsForPeriod(Carbon $startDate, Carbon $endDate): array
    {
        $busyVehicleIds = RentalOrder::whereIn('status', ['confirmed', 'active'])
            ->where('start_date', '<=', $endDate)
            ->where('end_date', '>=', $startDate)
            ->pluck('vehicle_id')
            ->toArray();

        $bookedVehicleIds = \App\Models\Booking::whereIn('status', ['pending', 'confirmed'])
            ->where('start_date', '<=', $endDate)
            ->where('end_date', '>=', $startDate)
            ->pluck('vehicle_id')
            ->toArray();

        return array_values(array_diff(
            Vehicle::where('is_active', true)
                ->where('status', 'available')
                ->pluck('id')
                ->toArray(),
            $busyVehicleIds,
            $bookedVehicleIds
        ));
    }

    protected function checkNoOverlappingReservations(int $vehicleId, Carbon $startDate, Carbon $endDate): bool
    {
        $hasOrderConflict = RentalOrder::where('vehicle_id', $vehicleId)
            ->whereIn('status', ['confirmed', 'active'])
            ->where('start_date', '<=', $endDate)
            ->where('end_date', '>=', $startDate)
            ->exists();

        if ($hasOrderConflict) {
            return false;
        }

        $hasBookingConflict = \App\Models\Booking::where('vehicle_id', $vehicleId)
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('start_date', '<=', $endDate)
            ->where('end_date', '>=', $startDate)
            ->exists();

        return !$hasBookingConflict;
    }

    public function getNextAvailableDate(int $vehicleId, Carbon $fromDate): ?Carbon
    {
        $occupiedPeriods = RentalOrder::where('vehicle_id', $vehicleId)
            ->whereIn('status', ['confirmed', 'active'])
            ->where('end_date', '>=', $fromDate)
            ->orderBy('start_date')
            ->get(['start_date', 'end_date']);

        $currentDate = $fromDate->copy();

        foreach ($occupiedPeriods as $period) {
            if ($currentDate->lt($period->start_date)) {
                return $currentDate;
            }
            if ($currentDate->lte($period->end_date)) {
                $currentDate = $period->end_date->copy()->addDay();
            }
        }

        return $currentDate;
    }
}

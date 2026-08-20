<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\RentalOrder;
use App\Models\Vehicle;
use App\Services\NotificationDispatcher;
use App\Services\PricingCalculator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BookingService
{
    public function __construct(
        protected PricingCalculator $pricing,
        protected NotificationDispatcher $notification,
    ) {}

    public function createBooking(array $data): Booking
    {
        $vehicle = Vehicle::findOrFail($data['vehicle_id']);
        $startDate = Carbon::parse($data['start_date']);
        $endDate = Carbon::parse($data['end_date']);

        if ($startDate->gte($endDate)) {
            throw new \InvalidArgumentException('End date must be after start date.');
        }

        if (!$this->checkAvailability($vehicle->id, $startDate, $endDate)) {
            throw new \RuntimeException('Vehicle is not available for the selected dates.');
        }

        $durationDays = max(1, $startDate->diffInDays($endDate));
        $pricing = $this->pricing->calculateOrderTotal(
            $vehicle,
            $startDate,
            $endDate,
            $data['rental_type'] ?? 'self_drive',
            $data['driver_daily_cost'] ?? null,
            $data['addon_ids'] ?? null,
            null,
            (float) ($data['tax_rate'] ?? 0.11)
        );

        return DB::transaction(function () use ($data, $pricing, $durationDays, $vehicle) {
            $booking = Booking::create([
                'customer_id' => $data['customer_id'],
                'vehicle_id' => $vehicle->id,
                'pickup_location_id' => $data['pickup_location_id'] ?? $vehicle->location_id,
                'return_location_id' => $data['return_location_id'] ?? $vehicle->location_id,
                'driver_id' => $data['driver_id'] ?? null,
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'duration_days' => $durationDays,
                'daily_rate_snapshot' => $pricing['base_daily_rate'],
                'subtotal' => $pricing['subtotal'],
                'discount_amount' => $pricing['discount_amount'],
                'tax_amount' => $pricing['tax_amount'],
                'total_amount' => $pricing['total'],
                'deposit_amount' => $data['deposit_amount'] ?? $vehicle->deposit_amount,
                'status' => 'pending_verification',
                'notes' => $data['notes'] ?? null,
                'created_by' => $data['created_by'] ?? auth()->id(),
            ]);

            if (!empty($data['voucher_ids'])) {
                $this->applyVouchers($booking, $data['voucher_ids'], $data['customer_id']);
            }

            return $booking;
        });
    }

    public function confirmBooking(Booking $booking): Booking
    {
        if ($booking->status !== 'pending_verification') {
            throw new \RuntimeException("Cannot confirm booking with status '{$booking->status}'.");
        }

        $vehicle = Vehicle::findOrFail($booking->vehicle_id);

        if (!$this->checkAvailability($vehicle->id, $booking->start_date, $booking->end_date, $booking->id)) {
            throw new \RuntimeException('Vehicle is no longer available for the selected dates.');
        }

        $booking->update(['status' => 'confirmed']);
        $vehicle->update(['status' => 'reserved']);

        $this->notification->sendBookingConfirmation($booking);

        return $booking->fresh();
    }

    public function cancelBooking(Booking $booking, ?string $reason = null): Booking
    {
        if (in_array($booking->status, ['converted', 'cancelled'])) {
            throw new \RuntimeException("Cannot cancel booking with status '{$booking->status}'.");
        }

        $previousStatus = $booking->status;

        $booking->update([
            'status' => 'cancelled',
            'notes' => $reason ? ($booking->notes ? "{$booking->notes}\nCancellation: {$reason}" : $reason) : $booking->notes,
        ]);

        if (in_array($previousStatus, ['confirmed', 'active'])) {
            $vehicle = Vehicle::findOrFail($booking->vehicle_id);
            if ($vehicle->status === 'reserved') {
                $vehicle->update(['status' => 'available']);
            }
        }

        return $booking->fresh();
    }

    public function convertToOrder(Booking $booking): RentalOrder
    {
        if (!in_array($booking->status, ['pending_verification', 'confirmed'])) {
            throw new \RuntimeException("Cannot convert booking with status '{$booking->status}' to order.");
        }

        return DB::transaction(function () use ($booking) {
            $order = RentalOrder::create([
                'booking_id' => $booking->id,
                'customer_id' => $booking->customer_id,
                'vehicle_id' => $booking->vehicle_id,
                'driver_id' => $booking->driver_id,
                'location_id' => $booking->pickup_location_id,
                'start_date' => $booking->start_date,
                'end_date' => $booking->end_date,
                'duration_days' => $booking->duration_days,
                'daily_rate_snapshot' => $booking->daily_rate_snapshot,
                'subtotal' => $booking->subtotal,
                'discount_total' => $booking->discount_amount,
                'tax_total' => $booking->tax_amount,
                'final_amount' => $booking->total_amount,
                'balance_due' => $booking->total_amount,
                'deposit_amount' => $booking->deposit_amount,
                'status' => 'ready_for_preparation',
                'payment_status' => 'unpaid',
                'notes' => $booking->notes,
                'created_by' => auth()->id(),
            ]);

            $booking->update(['status' => 'converted']);

            Vehicle::where('id', $booking->vehicle_id)
                ->update(['status' => 'reserved']);

            return $order;
        });
    }

    public function checkAvailability(int $vehicleId, Carbon $startDate, Carbon $endDate, ?int $excludeBookingId = null): bool
    {
        $query = RentalOrder::where('vehicle_id', $vehicleId)
            ->whereIn('status', ['ready_for_preparation', 'preparing', 'ready_for_handover', 'checked_out', 'active', 'extension_requested', 'return_due', 'overdue'])
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($q2) use ($startDate, $endDate) {
                        $q2->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                    });
            });

        if ($excludeBookingId) {
            $query->where('booking_id', '!=', $excludeBookingId);
        }

        $hasConflict = $query->exists();

        if ($hasConflict) {
            return false;
        }

        $bookingQuery = Booking::where('vehicle_id', $vehicleId)
            ->whereIn('status', ['pending_verification', 'pending_payment', 'confirmed', 'hold'])
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($q2) use ($startDate, $endDate) {
                        $q2->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                    });
            });

        if ($excludeBookingId) {
            $bookingQuery->where('id', '!=', $excludeBookingId);
        }

        return !$bookingQuery->exists();
    }

    protected function applyVouchers(Booking $booking, array $voucherIds, int $customerId): void
    {
        $voucherService = app(VoucherService::class);

        foreach ($voucherIds as $voucherId) {
            $voucherService->applyToBooking($booking, $voucherId, $customerId);
        }
    }
}

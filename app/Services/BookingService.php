<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\CorporateAccount;
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
        $screening = app(BlacklistService::class)->checkCustomerBeforeBooking((int) $data['customer_id']);
        if ($screening['blocked'] ?? true) {
            throw new \RuntimeException($screening['reason'] ?? 'Customer tidak lolos pemeriksaan risiko.');
        }

        $vehicle = Vehicle::findOrFail($data['vehicle_id']);
        $startDate = Carbon::parse($data['start_date']);
        $endDate = Carbon::parse($data['end_date']);

        // Batas kredit akun korporat: tolak booking jika piutang + estimasi order melebihi limit.
        $corporateCustomer = Customer::find($data['customer_id']);
        if ($corporateCustomer?->corporate_account_id) {
            $account = CorporateAccount::find($corporateCustomer->corporate_account_id);
            if ($account) {
                $estimate = (float) $vehicle->daily_rate * max(1, $startDate->diffInDays($endDate));
                $credit = app(CorporateBillingService::class)->checkCreditLimit($account, $estimate);
                abort_unless($credit['allowed'], 422, "Limit kredit akun korporat tidak cukup. Tersedia: Rp ".number_format($credit['available'], 0, ',', '.'));
            }
        }

        if ($startDate->gte($endDate)) {
            throw new \InvalidArgumentException('End date must be after start date.');
        }

        if (!$this->checkAvailability($vehicle->id, $startDate, $endDate)) {
            throw new \RuntimeException('Vehicle is not available for the selected dates.');
        }

        // Dokumen kendaraan harus valid selama masa sewa (STNK/pajak/KIR).
        $expiredDocs = $vehicle->expiredDocuments($endDate);
        if ($expiredDocs !== []) {
            throw new \RuntimeException('Dokumen kendaraan kedaluwarsa selama periode sewa: '.implode(', ', $expiredDocs).'. Perbarui dokumen terlebih dahulu.');
        }

        // SIM penyewa harus berlaku sampai selesai sewa (self-drive maupun dengan supir).
        $bookingCustomer = Customer::find($data['customer_id']);
        if ($bookingCustomer?->sim_expiry_date && $bookingCustomer->sim_expiry_date->lt($endDate)) {
            throw new \RuntimeException("SIM penyewa kedaluwarsa pada {$bookingCustomer->sim_expiry_date->format('d/m/Y')} — perpanjang SIM sebelum booking.");
        }

        $durationDays = max(1, $startDate->diffInDays($endDate));
        $pricing = app(PricingEngine::class)->calculateRentalPrice($vehicle,$startDate->toDateString(),$endDate->toDateString(),$data['rental_type']??'self_drive',$data['addon_ids']??[],$data['promo_code']??null);

        // Rate card korporat: diskon % akun diterapkan setelah promo.
        $corporateCustomer = Customer::find($data['customer_id']);
        if ($corporateCustomer?->corporate_account_id) {
            $account = CorporateAccount::find($corporateCustomer->corporate_account_id);
            if ($account && (float) $account->discount_percent > 0) {
                $extraDiscount = round((float) $pricing['after_discount'] * (float) $account->discount_percent / 100, 2);
                $pricing['discount_amount'] = round((float) $pricing['discount_amount'] + $extraDiscount, 2);
                $pricing['after_discount'] = round(max(0.0, (float) $pricing['subtotal'] - $pricing['discount_amount']), 2);
                $pricing['tax_amount'] = round($pricing['after_discount'] * app(PricingEngine::class)->getTaxRate(), 2);
                $pricing['total'] = round($pricing['after_discount'] + $pricing['tax_amount'], 2);
                $pricing['breakdown']['corporate_discount_percent'] = (float) $account->discount_percent;
            }
        }

        return DB::transaction(function () use ($data, $pricing, $durationDays, $vehicle) {
            $booking = Booking::create([
                'customer_id' => $data['customer_id'],
                'group_booking_id' => $data['group_booking_id'] ?? null,
                'vehicle_id' => $vehicle->id,
                'pickup_location_id' => $data['pickup_location_id'] ?? $vehicle->location_id,
                'return_location_id' => $data['return_location_id'] ?? $vehicle->location_id,
                'pickup_city' => $data['pickup_city'] ?? null,
                'return_city' => $data['return_city'] ?? null,
                'relocation_fee' => $pricing['breakdown']['relocation_fee'] ?? ($data['relocation_fee'] ?? 0),
                'driver_id' => $data['driver_id'] ?? null,
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'duration_days' => $durationDays,
                'daily_rate_snapshot' => $pricing['effective_daily_rate'],
                'subtotal' => $pricing['subtotal'],
                'discount_amount' => $pricing['discount_amount'],
                'tax_amount' => $pricing['tax_amount'],
                'total_amount' => $pricing['total'],
                'deposit_amount' => $data['deposit_amount'] ?? $vehicle->deposit_amount,
                'status' => 'pending_verification',
                'notes' => $data['notes'] ?? null,
                'source' => $data['source'] ?? 'admin',
                'session_id' => $data['session_id'] ?? null,
                'addon_ids' => array_values($data['addon_ids'] ?? []),
                'pricing_snapshot' => $pricing,
                'created_by' => $data['created_by'] ?? auth()->id(),
            ]);

            // Konversi hold aktif milik session ini → converted (mengunci slot).
            if (!empty($data['session_id'])) {
                \App\Models\BookingHold::where('session_id', $data['session_id'])
                    ->where('vehicle_id', $vehicle->id)
                    ->where('status', 'active')
                    ->update(['status' => 'converted', 'booking_id' => $booking->id]);
            }

            // Tandai abandoned booking terkait session sebagai recovered.
            if (!empty($data['session_id'])) {
                \App\Models\AbandonedBooking::where('session_id', $data['session_id'])
                    ->open()
                    ->update(['status' => 'recovered', 'recovered_booking_id' => $booking->id]);
            }

            if (!empty($data['voucher_ids'])) {
                $this->applyVouchers($booking, $data['voucher_ids'], $data['customer_id']);
            }

            try {
                app(\App\Services\WebhookDispatchService::class)->dispatch('booking.created', [
                    'booking_number' => $booking->booking_number,
                    'customer_id' => $booking->customer_id,
                    'vehicle_id' => $booking->vehicle_id,
                    'start_date' => $booking->start_date?->toDateString(),
                    'end_date' => $booking->end_date?->toDateString(),
                    'total_amount' => (float) $booking->total_amount,
                    'status' => $booking->status,
                ]);
            } catch (\Throwable $e) {
                report($e);
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

        $booking->update(['status' => 'confirmed', 'confirmed_at' => now()]);
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

            foreach (($booking->pricing_snapshot['addon_details'] ?? []) as $addon) {
                $order->items()->create([
                    'addon_id' => $addon['id'],
                    'name' => $addon['name'],
                    'quantity' => 1,
                    'unit_price' => $addon['unit_price'],
                    'total_price' => $addon['total'],
                    'type' => 'addon',
                ]);
            }

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

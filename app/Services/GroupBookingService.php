<?php

namespace App\Services;

use App\Models\GroupBooking;
use App\Models\Vehicle;
use Illuminate\Support\Facades\DB;

class GroupBookingService
{
    public function __construct(
        protected PricingEngine $pricing,
        protected AvailabilityService $availability
    ) {}

    public function createInquiry(array $data): GroupBooking
    {
        return GroupBooking::create($data + ['status' => 'inquiry']);
    }

    /**
     * Buat quotation untuk seluruh unit group booking: cari unit available
     * sebanyak units_needed pada rentanggal, hitung harga per unit.
     */
    public function quote(GroupBooking $group): GroupBooking
    {
        abort_if(in_array($group->status, ['confirmed', 'completed']), 422, 'Group booking sudah dikonfirmasi.');
        abort_unless($group->start_date && $group->end_date && $group->category_id && $group->location_id, 422, 'Lengkapi kategori, lokasi, dan tanggal terlebih dahulu.');

        $vehicles = Vehicle::query()
            ->where('category_id', $group->category_id)
            ->where('location_id', $group->location_id)
            ->where('is_active', true)
            ->available()
            ->orderBy('daily_rate')
            ->limit($group->units_needed)
            ->get();

        if ($vehicles->count() < min($group->units_needed, 3)) {
            throw new \RuntimeException('Stok armada tidak cukup untuk group booking ini. Tersedia ' . $vehicles->count() . ' unit.');
        }

        $total = 0.0;
        $details = [];

        foreach ($vehicles as $vehicle) {
            $quote = $this->pricing->calculateRentalPrice(
                $vehicle,
                $group->start_date->toDateString(),
                $group->end_date->toDateString()
            );

            // Diskon volume bertingkat untuk group booking.
            $volumeDiscount = $this->volumeDiscountPercent($vehicles->count());
            $discounted = round($quote['total'] * (1 - $volumeDiscount / 100), 2);
            $total += $discounted;

            $details[] = [
                'vehicle_id' => $vehicle->id,
                'vehicle_name' => $vehicle->name,
                'original_total' => $quote['total'],
                'volume_discount_pct' => $volumeDiscount,
                'total' => $discounted,
            ];
        }

        $group->update([
            'quoted_total' => round($total, 2),
            'status' => 'quoted',
            'notes' => $group->notes . "\n[QUOTE " . now()->toDateTimeString() . '] ' .
                json_encode(['details' => $details, 'grand_total' => round($total, 2)], JSON_UNESCAPED_UNICODE),
        ]);

        return $group->refresh();
    }

    public function confirm(GroupBooking $group): GroupBooking
    {
        abort_unless($group->status === 'quoted', 422, 'Buat quotation terlebih dahulu.');

        $group->update(['status' => 'confirmed']);

        app(NotificationDispatcher::class)->dispatch('group_booking_confirmed', $group, [
            'code' => $group->code,
            'event_name' => $group->event_name,
            'quoted_total' => $group->quoted_total,
        ]);

        return $group;
    }

    protected function volumeDiscountPercent(int $units): float
    {
        return match (true) {
            $units >= 10 => 15.0,
            $units >= 5 => 10.0,
            $units >= 3 => 5.0,
            default => 0.0,
        };
    }
}

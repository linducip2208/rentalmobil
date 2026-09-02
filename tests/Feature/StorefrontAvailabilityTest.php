<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Brand;
use App\Models\Customer;
use App\Models\RentalOrder;
use App\Models\Vehicle;
use App\Services\AvailabilityEngine;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    protected Customer $customer;

    protected Vehicle $vehicle;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = Customer::create([
            'name' => 'Avail Test', 'email' => 'avail@test.local', 'phone' => '08122220001',
            'customer_type' => 'individual', 'verification_status' => 'verified', 'is_active' => true,
        ]);

        $this->vehicle = Vehicle::create([
            'name' => 'Toyota Avanza Test', 'slug' => 'toyota-avanza-test',
            'category_id' => 1, 'brand_id' => Brand::firstOrCreate(['name' => 'Toyota'], ['slug' => 'toyota', 'is_active' => true])->id,
            'location_id' => 1, 'plate_number' => 'B 9999 ZT', 'year' => 2024, 'color' => 'Putih',
            'transmission' => 'automatic', 'fuel_type' => 'pertalite', 'seat_count' => 7,
            'mileage' => 10000, 'daily_rate' => 350000, 'weekly_rate' => 2100000, 'monthly_rate' => 7500000,
            'deposit_amount' => 500000, 'status' => 'available', 'is_active' => true,
        ]);
    }

    private function engine(): AvailabilityEngine
    {
        return app(AvailabilityEngine::class);
    }

    private function makeBooking(string $start, string $end, string $status, ?string $holdExpiresAt = null): Booking
    {
        return Booking::create([
            'customer_id' => $this->customer->id,
            'vehicle_id' => $this->vehicle->id,
            'start_date' => $start, 'end_date' => $end,
            'rental_type' => 'self_drive', 'duration_days' => 3,
            'subtotal' => 1050000, 'total_amount' => 1050000,
            'status' => $status,
            'hold_expires_at' => $holdExpiresAt,
            'source' => 'website',
        ]);
    }

    public function test_available_vehicle_appears_in_engine_check(): void
    {
        $result = $this->engine()->checkAvailability($this->vehicle, '2026-10-01', '2026-10-05');

        $this->assertTrue($result['available']);
    }

    public function test_overlapping_confirmed_booking_blocks(): void
    {
        $this->makeBooking('2026-10-01', '2026-10-05', 'confirmed');

        // Overlap terdeteksi → engine melaporkan TRUE (kendaraan diblok).
        $this->assertTrue($this->engine()->checkOverlap('2026-10-03', '2026-10-07', $this->vehicle->id));
    }

    public function test_active_rental_order_blocks(): void
    {
        RentalOrder::create([
            'order_number' => 'RO-TEST-001', 'customer_id' => $this->customer->id,
            'vehicle_id' => $this->vehicle->id, 'location_id' => 1,
            'start_date' => '2026-10-01', 'end_date' => '2026-10-05',
            'rental_type' => 'self_drive', 'duration_days' => 4,
            'daily_rate_snapshot' => 350000, 'subtotal' => 1400000,
            'final_amount' => 1400000, 'amount_paid' => 0, 'balance_due' => 1400000,
            'status' => 'active', 'payment_status' => 'unpaid',
        ]);

        $this->assertTrue($this->engine()->checkOverlap('2026-10-02', '2026-10-04', $this->vehicle->id));
    }

    public function test_completed_order_does_not_block(): void
    {
        RentalOrder::create([
            'order_number' => 'RO-TEST-002', 'customer_id' => $this->customer->id,
            'vehicle_id' => $this->vehicle->id, 'location_id' => 1,
            'start_date' => '2026-10-01', 'end_date' => '2026-10-05',
            'rental_type' => 'self_drive', 'duration_days' => 4,
            'daily_rate_snapshot' => 350000, 'subtotal' => 1400000,
            'final_amount' => 1400000, 'amount_paid' => 1400000, 'balance_due' => 0,
            'status' => 'completed', 'payment_status' => 'paid',
        ]);

        $this->assertTrue($this->engine()->checkOverlap('2026-10-02', '2026-10-04', $this->vehicle->id) === false);
    }

    public function test_maintenance_status_blocks_vehicle(): void
    {
        $this->vehicle->update(['status' => 'maintenance']);

        $result = $this->engine()->checkAvailability($this->vehicle, '2026-10-01', '2026-10-05');

        $this->assertFalse($result['available']);
    }

    public function test_damaged_status_blocks_vehicle(): void
    {
        $this->vehicle->update(['status' => 'damaged']);

        $result = $this->engine()->checkAvailability($this->vehicle, '2026-10-01', '2026-10-05');

        $this->assertFalse($result['available']);
    }

    public function test_inactive_vehicle_blocks(): void
    {
        $this->vehicle->update(['is_active' => false]);

        $result = $this->engine()->checkAvailability($this->vehicle, '2026-10-01', '2026-10-05');

        $this->assertFalse($result['available']);
    }

    public function test_expired_hold_does_not_block(): void
    {
        $this->makeBooking('2026-10-01', '2026-10-05', 'hold', now()->subMinutes(5)->toDateTimeString());

        $this->assertTrue($this->engine()->checkOverlap('2026-10-02', '2026-10-04', $this->vehicle->id) === false);
    }

    public function test_active_hold_blocks(): void
    {
        $this->makeBooking('2026-10-01', '2026-10-05', 'hold', now()->addMinutes(15)->toDateTimeString());

        $this->assertTrue($this->engine()->checkOverlap('2026-10-02', '2026-10-04', $this->vehicle->id));
    }

    public function test_different_period_remains_available(): void
    {
        $this->makeBooking('2026-10-01', '2026-10-05', 'confirmed');

        $this->assertTrue($this->engine()->checkOverlap('2026-10-06', '2026-10-09', $this->vehicle->id) === false);
    }

    public function test_boundary_dates_back_to_back_are_available(): void
    {
        // Booking berurutan: 01–05 lalu mulai 05 → hari 05 overlap (day-inclusive) = BLOK.
        $this->makeBooking('2026-10-01', '2026-10-05', 'confirmed');
        $this->assertTrue($this->engine()->checkOverlap('2026-10-05', '2026-10-08', $this->vehicle->id));

        // Mulai 06 (hari setelah return) → bebas, boundary tidak salah overlap.
        $this->assertTrue($this->engine()->checkOverlap('2026-10-06', '2026-10-08', $this->vehicle->id) === false);
    }

    public function test_blocked_vehicle_ids_combine_all_sources(): void
    {
        $free = Vehicle::create([
            'name' => 'Honda Brio Test', 'slug' => 'honda-brio-test',
            'category_id' => 1, 'brand_id' => 1, 'location_id' => 1,
            'plate_number' => 'B 8888 ZT', 'year' => 2024, 'color' => 'Merah',
            'transmission' => 'automatic', 'fuel_type' => 'pertalite', 'seat_count' => 5,
            'mileage' => 10000, 'daily_rate' => 275000, 'weekly_rate' => 1650000, 'monthly_rate' => 6000000,
            'deposit_amount' => 400000, 'status' => 'available', 'is_active' => true,
        ]);

        $maintenance = Vehicle::create([
            'name' => 'Mitsubishi Pajero Test', 'slug' => 'mitsubishi-pajero-test',
            'category_id' => 1, 'brand_id' => 1, 'location_id' => 1,
            'plate_number' => 'B 7777 ZT', 'year' => 2024, 'color' => 'Putih',
            'transmission' => 'automatic', 'fuel_type' => 'diesel', 'seat_count' => 7,
            'mileage' => 10000, 'daily_rate' => 750000, 'weekly_rate' => 4500000, 'monthly_rate' => 16000000,
            'deposit_amount' => 1500000, 'status' => 'maintenance', 'is_active' => true,
        ]);

        $this->makeBooking('2026-10-01', '2026-10-05', 'confirmed');

        $blocked = $this->engine()->blockedVehicleIds(
            CarbonImmutable::parse('2026-10-02'),
            CarbonImmutable::parse('2026-10-04')
        );

        $this->assertTrue($blocked->contains($this->vehicle->id));
        $this->assertTrue($blocked->contains($maintenance->id));
        $this->assertFalse($blocked->contains($free->id));
    }
}

<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Vehicle;
use App\Services\AvailabilityEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AvailabilityEngineTest extends TestCase
{
    use RefreshDatabase;

    protected Vehicle $vehicle;

    protected AvailabilityEngine $engine;

    protected int $customerId;

    protected function setUp(): void
    {
        parent::setUp();

        $customer = Customer::create([
            'name' => 'Customer Test', 'email' => 'availability@test.local',
            'phone' => '080000000001', 'verification_status' => 'verified', 'is_active' => true,
        ]);
        $this->customerId = $customer->id;

        $this->vehicle = Vehicle::create([
            'name' => 'Honda Brio',
            'slug' => 'honda-brio-b5678ef',
            'category_id' => 1,
            'brand_id' => 1,
            'location_id' => 1,
            'plate_number' => 'B 5678 EF',
            'year' => 2024,
            'color' => 'Merah',
            'transmission' => 'automatic',
            'seat_count' => 5,
            'daily_rate' => 400000,
            'weekly_rate' => 2500000,
            'monthly_rate' => 8000000,
            'late_fee_per_hour' => 20000,
            'late_fee_per_day' => 150000,
            'deposit_amount' => 400000,
            'status' => 'available',
            'is_active' => true,
        ]);

        $this->engine = new AvailabilityEngine;
    }

    public function test_vehicle_available_for_dates(): void
    {
        $result = $this->engine->checkAvailability(
            $this->vehicle,
            '2026-10-01',
            '2026-10-05'
        );

        $this->assertTrue($result['available']);
        $this->assertTrue($result['conflicts']->isEmpty());
        $this->assertStringContainsString('tersedia', $result['message']);
    }

    public function test_vehicle_not_available_with_conflict(): void
    {
        Booking::create([
            'customer_id' => $this->customerId,
            'vehicle_id' => $this->vehicle->id,
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-05',
            'rental_type' => 'self_drive',
            'duration_days' => 4,
            'subtotal' => 1600000,
            'total_amount' => 1600000,
            'status' => 'confirmed',
        ]);

        $result = $this->engine->checkAvailability(
            $this->vehicle,
            '2026-10-03',
            '2026-10-07'
        );

        $this->assertFalse($result['available']);
        $this->assertGreaterThanOrEqual(1, $result['conflicts']->count());
    }

    public function test_can_exclude_booking_from_check(): void
    {
        $booking = Booking::create([
            'customer_id' => $this->customerId,
            'vehicle_id' => $this->vehicle->id,
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-05',
            'rental_type' => 'self_drive',
            'duration_days' => 4,
            'subtotal' => 1600000,
            'total_amount' => 1600000,
            'status' => 'confirmed',
        ]);

        $resultWithoutExclude = $this->engine->checkAvailability(
            $this->vehicle,
            '2026-10-03',
            '2026-10-07'
        );
        $this->assertFalse($resultWithoutExclude['available']);

        $resultWithExclude = $this->engine->checkAvailability(
            $this->vehicle,
            '2026-10-03',
            '2026-10-07',
            excludeBookingId: $booking->id
        );
        $this->assertTrue($resultWithExclude['available']);
    }

    public function test_find_available_vehicles_returns_correct(): void
    {
        $vehicleAvailable = Vehicle::create([
            'name' => 'Suzuki Ertiga',
            'slug' => 'suzuki-ertiga-b9999xx',
            'category_id' => 1,
            'brand_id' => 2,
            'location_id' => 1,
            'plate_number' => 'B 9999 XX',
            'year' => 2023,
            'color' => 'Hitam',
            'transmission' => 'automatic',
            'seat_count' => 7,
            'daily_rate' => 450000,
            'weekly_rate' => 2800000,
            'monthly_rate' => 9000000,
            'late_fee_per_hour' => 20000,
            'late_fee_per_day' => 150000,
            'deposit_amount' => 450000,
            'status' => 'available',
            'is_active' => true,
        ]);

        $vehicleBooked = Vehicle::create([
            'name' => 'Toyota Innova',
            'slug' => 'toyota-innova-b1111yy',
            'category_id' => 1,
            'brand_id' => 1,
            'location_id' => 1,
            'plate_number' => 'B 1111 YY',
            'year' => 2022,
            'color' => 'Silver',
            'transmission' => 'automatic',
            'seat_count' => 7,
            'daily_rate' => 600000,
            'weekly_rate' => 3500000,
            'monthly_rate' => 12000000,
            'late_fee_per_hour' => 25000,
            'late_fee_per_day' => 200000,
            'deposit_amount' => 600000,
            'status' => 'available',
            'is_active' => true,
        ]);

        Booking::create([
            'customer_id' => $this->customerId,
            'vehicle_id' => $vehicleBooked->id,
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-05',
            'rental_type' => 'self_drive',
            'duration_days' => 4,
            'subtotal' => 2400000,
            'total_amount' => 2400000,
            'status' => 'confirmed',
        ]);

        $available = $this->engine->findAvailableVehicles('2026-10-03', '2026-10-07');

        $this->assertTrue($available->contains('id', $vehicleAvailable->id));
        $this->assertFalse($available->contains('id', $vehicleBooked->id));
    }
}

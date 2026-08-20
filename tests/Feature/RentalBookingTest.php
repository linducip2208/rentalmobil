<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\AvailabilityEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RentalBookingTest extends TestCase
{
    use RefreshDatabase;

    protected function createCustomer(): Customer
    {
        return Customer::create([
            'name' => 'Budi Santoso',
            'customer_type' => 'individual',
            'email' => 'budi@test.com',
            'phone' => '081234567890',
            'trust_score' => 100,
            'total_spent' => 0,
            'total_orders' => 0,
            'verification_status' => 'verified',
            'is_active' => true,
        ]);
    }

    protected function createVehicle(array $overrides = []): Vehicle
    {
        return Vehicle::create(array_merge([
            'name' => 'Toyota Avanza',
            'slug' => 'toyota-avanza-b1234cd',
            'category_id' => 1,
            'brand_id' => 1,
            'location_id' => 1,
            'plate_number' => 'B 1234 CD',
            'year' => 2023,
            'color' => 'Putih',
            'transmission' => 'automatic',
            'seat_count' => 7,
            'daily_rate' => 500000,
            'weekly_rate' => 3000000,
            'monthly_rate' => 10000000,
            'late_fee_per_hour' => 25000,
            'late_fee_per_day' => 200000,
            'deposit_amount' => 500000,
            'status' => 'available',
            'is_active' => true,
        ], $overrides));
    }

    public function test_can_create_booking(): void
    {
        $customer = $this->createCustomer();
        $vehicle = $this->createVehicle();

        $booking = Booking::create([
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'pickup_location_id' => $vehicle->location_id,
            'return_location_id' => $vehicle->location_id,
            'start_date' => now()->addDays(1),
            'end_date' => now()->addDays(4),
            'estimated_return_date' => now()->addDays(4),
            'rental_type' => 'self_drive',
            'duration_days' => 3,
            'daily_rate_snapshot' => $vehicle->daily_rate,
            'subtotal' => 1500000,
            'total_amount' => 1500000,
            'deposit_amount' => $vehicle->deposit_amount,
            'status' => 'pending_verification',
            'source' => 'admin',
        ]);

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'status' => 'pending_verification',
        ]);
    }

    public function test_booking_generates_unique_number(): void
    {
        $customer = $this->createCustomer();
        $vehicle = $this->createVehicle();

        $booking1 = Booking::create([
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'start_date' => now()->addDays(1),
            'end_date' => now()->addDays(4),
            'rental_type' => 'self_drive',
            'duration_days' => 3,
            'subtotal' => 1500000,
            'total_amount' => 1500000,
            'status' => 'pending_verification',
        ]);

        $booking2 = Booking::create([
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'start_date' => now()->addDays(10),
            'end_date' => now()->addDays(13),
            'rental_type' => 'self_drive',
            'duration_days' => 3,
            'subtotal' => 1500000,
            'total_amount' => 1500000,
            'status' => 'pending_verification',
        ]);

        $this->assertMatchesRegularExpression('/^BKG-\d{8}-[A-Z0-9]{6}$/', $booking1->booking_number);
        $this->assertNotEquals($booking1->booking_number, $booking2->booking_number);
    }

    public function test_booking_overlap_detection(): void
    {
        $customer = $this->createCustomer();
        $vehicle = $this->createVehicle();

        Booking::create([
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'start_date' => '2026-09-01',
            'end_date' => '2026-09-05',
            'rental_type' => 'self_drive',
            'duration_days' => 4,
            'subtotal' => 2000000,
            'total_amount' => 2000000,
            'status' => 'confirmed',
        ]);

        $engine = new AvailabilityEngine();

        $result = $engine->checkAvailability($vehicle, '2026-09-03', '2026-09-07');
        $this->assertFalse($result['available']);
        $this->assertTrue($result['conflicts']->isNotEmpty());

        $result = $engine->checkAvailability($vehicle, '2026-09-06', '2026-09-10');
        $this->assertTrue($result['available']);
    }

    public function test_can_cancel_booking(): void
    {
        $customer = $this->createCustomer();
        $vehicle = $this->createVehicle();

        $booking = Booking::create([
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'start_date' => now()->addDays(5),
            'end_date' => now()->addDays(8),
            'rental_type' => 'self_drive',
            'duration_days' => 3,
            'subtotal' => 1500000,
            'total_amount' => 1500000,
            'status' => 'confirmed',
        ]);

        $booking->update([
            'status' => 'cancelled',
            'cancellation_reason' => 'Customer berubah pikiran',
            'cancelled_at' => now(),
        ]);

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'cancelled',
        ]);
        $this->assertNotNull($booking->fresh()->cancelled_at);
    }

    public function test_hold_expires_correctly(): void
    {
        $customer = $this->createCustomer();
        $vehicle = $this->createVehicle();

        $booking = Booking::create([
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'start_date' => now()->addDays(2),
            'end_date' => now()->addDays(5),
            'rental_type' => 'self_drive',
            'duration_days' => 3,
            'subtotal' => 1500000,
            'total_amount' => 1500000,
            'status' => 'hold',
            'hold_expires_at' => now()->subMinutes(5),
        ]);

        $this->assertTrue($booking->status === 'hold');
        $this->assertTrue($booking->hold_expires_at->isPast());

        $engine = new AvailabilityEngine();
        $expiredCount = $engine->releaseExpiredHolds();
        $this->assertEquals(1, $expiredCount);

        $booking->refresh();
        $this->assertEquals('expired', $booking->status);
    }
}

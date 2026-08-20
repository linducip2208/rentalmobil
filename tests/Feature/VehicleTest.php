<?php

namespace Tests\Feature;

use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehicleTest extends TestCase
{
    use RefreshDatabase;

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

    public function test_can_create_vehicle(): void
    {
        $vehicle = $this->createVehicle([
            'name' => 'Honda Civic',
            'plate_number' => 'B 9999 ZZ',
            'slug' => 'honda-civic-b9999zz',
        ]);

        $this->assertDatabaseHas('vehicles', [
            'id' => $vehicle->id,
            'name' => 'Honda Civic',
            'plate_number' => 'B 9999 ZZ',
        ]);
    }

    public function test_vehicle_has_relationships(): void
    {
        $vehicle = $this->createVehicle();

        $this->assertEmpty($vehicle->bookings);
        $this->assertEmpty($vehicle->rentalOrders);
        $this->assertEmpty($vehicle->maintenanceLogs);

        $this->assertTrue($vehicle->bookings()->count() === 0);
        $this->assertTrue($vehicle->rentalOrders()->count() === 0);
        $this->assertTrue($vehicle->maintenanceLogs()->count() === 0);
    }

    public function test_vehicle_statuses(): void
    {
        $vehicle = $this->createVehicle(['status' => 'available']);
        $this->assertEquals('available', $vehicle->status);

        $vehicle->update(['status' => 'rented']);
        $this->assertEquals('rented', $vehicle->fresh()->status);

        $vehicle->update(['status' => 'maintenance']);
        $this->assertEquals('maintenance', $vehicle->fresh()->status);
    }

    public function test_available_scope(): void
    {
        $this->createVehicle(['status' => 'available', 'is_active' => true]);
        $this->createVehicle(['status' => 'rented', 'is_active' => true]);
        $this->createVehicle(['status' => 'available', 'is_active' => false]);

        $available = Vehicle::available()->get();
        $this->assertCount(1, $available);
        $this->assertEquals('available', $available->first()->status);
        $this->assertTrue($available->first()->is_active);
    }
}

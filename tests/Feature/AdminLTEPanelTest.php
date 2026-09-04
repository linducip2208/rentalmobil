<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Customer;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLTEPanelTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::create([
            'name' => 'LTE Owner', 'email' => 'lte-owner@test.local', 'phone' => '08155550001',
            'password' => bcrypt('password'), 'role' => 'owner', 'is_active' => true,
        ]);
    }

    private function makeVehicle(string $name = 'Avanza LTE', string $plate = 'B 4100 LT', array $extra = []): Vehicle
    {
        return Vehicle::create(array_merge([
            'name' => $name, 'slug' => strtolower(str_replace(' ', '-', $name)),
            'category_id' => 1, 'brand_id' => Brand::firstOrCreate(['name' => 'Toyota'], ['slug' => 'toyota', 'is_active' => true])->id,
            'location_id' => 1, 'plate_number' => $plate, 'year' => 2024, 'color' => 'Putih',
            'transmission' => 'automatic', 'fuel_type' => 'pertalite', 'seat_count' => 7,
            'mileage' => 10000, 'daily_rate' => 350000, 'weekly_rate' => 2100000, 'monthly_rate' => 7500000,
            'deposit_amount' => 500000, 'status' => 'available', 'is_active' => true,
        ], $extra));
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/lte')->assertRedirect();
    }

    public function test_driver_cannot_access_restricted_lte_pages(): void
    {
        $driver = User::create([
            'name' => 'LTE Driver', 'email' => 'lte-driver@test.local', 'phone' => '08155550002',
            'password' => bcrypt('password'), 'role' => 'driver', 'is_active' => true,
        ]);

        // Dashboard boleh (sesuai EnsureAdminRoleAccess), modul terlarang → 403.
        $this->actingAs($driver)->get('/lte')->assertOk();
        $this->actingAs($driver)->get('/lte/vehicles')->assertForbidden();
        $this->actingAs($driver)->get('/lte/invoices')->assertForbidden();
        $this->actingAs($driver)->get('/lte/customers')->assertForbidden();
    }

    public function test_owner_can_view_dashboard_and_core_pages(): void
    {
        $this->actingAs($this->owner);

        $this->get('/lte')->assertOk()->assertSee('Armada Tersedia');
        $this->get('/lte/vehicles')->assertOk()->assertSee('Tambah Kendaraan');
        $this->get('/lte/vehicles/create')->assertOk();
        $this->get('/lte/bookings')->assertOk();
        $this->get('/lte/orders')->assertOk();
        $this->get('/lte/customers')->assertOk();
        $this->get('/lte/invoices')->assertOk();
    }

    public function test_owner_can_create_edit_and_delete_vehicle(): void
    {
        $this->actingAs($this->owner);

        $category = Category::firstOrCreate(['slug' => 'mpv-test'], ['name' => 'MPV Test', 'is_active' => true]);
        $brand = Brand::firstOrCreate(['name' => 'Toyota'], ['slug' => 'toyota', 'is_active' => true]);

        $response = $this->post('/lte/vehicles', [
            'name' => 'Daihatsu Xenia LTE', 'category_id' => $category->id, 'brand_id' => $brand->id,
            'location_id' => 1, 'plate_number' => 'L 4101 LT', 'year' => 2024, 'color' => 'Biru',
            'transmission' => 'manual', 'fuel_type' => 'pertalite', 'seat_count' => 7,
            'mileage' => 5000, 'daily_rate' => 300000, 'weekly_rate' => 1800000, 'monthly_rate' => 6500000,
            'deposit_amount' => 400000, 'status' => 'available', 'is_active' => '1',
        ]);
        $response->assertRedirect('/lte/vehicles');

        $vehicle = Vehicle::where('plate_number', 'L 4101 LT')->firstOrFail();
        $this->assertSame('Daihatsu Xenia LTE', $vehicle->name);
        $this->assertStringContainsString('daihatsu-xenia-lte', $vehicle->slug);

        $this->put('/lte/vehicles/'.$vehicle->id, [
            'name' => 'Daihatsu Xenia LTE', 'category_id' => $category->id, 'brand_id' => $brand->id,
            'location_id' => 1, 'plate_number' => 'L 4101 LT', 'year' => 2024, 'color' => 'Hijau',
            'transmission' => 'manual', 'fuel_type' => 'pertalite', 'seat_count' => 7,
            'mileage' => 6000, 'daily_rate' => 320000, 'weekly_rate' => 1900000, 'monthly_rate' => 6800000,
            'deposit_amount' => 400000, 'status' => 'maintenance', 'is_active' => '1',
        ])->assertRedirect('/lte/vehicles');

        $this->assertSame('maintenance', $vehicle->fresh()->status);
        $this->assertSame(320000.0, (float) $vehicle->fresh()->daily_rate);

        $this->delete('/lte/vehicles/'.$vehicle->id)->assertRedirect('/lte/vehicles');
        $this->assertTrue($vehicle->fresh()->trashed());
    }

    public function test_booking_actions_route_through_service(): void
    {
        $this->actingAs($this->owner);

        $vehicle = $this->makeVehicle();
        $customer = Customer::create([
            'name' => 'LTE Cust', 'email' => 'lte-cust@test.local', 'phone' => '08155550003',
            'customer_type' => 'individual', 'verification_status' => 'verified', 'is_active' => true,
        ]);

        $booking = Booking::create([
            'customer_id' => $customer->id, 'vehicle_id' => $vehicle->id,
            'pickup_location_id' => 1, 'return_location_id' => 1,
            'start_date' => '2026-10-01', 'end_date' => '2026-10-05',
            'rental_type' => 'self_drive', 'duration_days' => 4,
            'daily_rate_snapshot' => 350000, 'subtotal' => 1400000,
            'tax_amount' => 154000, 'total_amount' => 1554000, 'deposit_amount' => 500000,
            'status' => 'pending_verification', 'source' => 'website',
        ]);

        // Show page
        $this->get('/lte/bookings/'.$booking->id)->assertOk()->assertSee($booking->booking_number);

        // Confirm via AdminLTE → BookingService transitions
        $this->post('/lte/bookings/'.$booking->id.'/confirm')->assertRedirect();
        $this->assertSame('confirmed', $booking->fresh()->status);
        $this->assertSame('reserved', $vehicle->fresh()->status);

        // Convert to order
        $this->post('/lte/bookings/'.$booking->id.'/convert')->assertRedirect();
        $this->assertSame('converted', $booking->fresh()->status);
        $this->assertDatabaseHas('rental_orders', ['booking_id' => $booking->id]);

        // Cancel converted booking → service rejects (no status mutation)
        $response = $this->post('/lte/bookings/'.$booking->id.'/cancel', ['reason' => 'uji coba']);
        $response->assertRedirect();
        $this->assertSame('converted', $booking->fresh()->status);
    }
}

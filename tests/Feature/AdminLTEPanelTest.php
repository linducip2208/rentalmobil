<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Deposit;
use App\Models\Driver;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\RentalOrder;
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

    public function test_payments_verify_and_reject_via_service(): void
    {
        $this->actingAs($this->owner);

        $vehicle = $this->makeVehicle('Avanza Pay', 'B 4200 PT');
        $customer = Customer::create([
            'name' => 'Pay Cust', 'email' => 'pay-cust@test.local', 'phone' => '08155550004',
            'customer_type' => 'individual', 'verification_status' => 'verified', 'is_active' => true,
        ]);

        $invoice = Invoice::create([
            'customer_id' => $customer->id, 'type' => 'rental',
            'subtotal' => 1000000, 'total_amount' => 1000000, 'balance_due' => 1000000, 'status' => 'issued',
        ]);

        $payment = Payment::create([
            'invoice_id' => $invoice->id, 'customer_id' => $customer->id,
            'amount' => 1000000, 'payment_date' => today(), 'reference_number' => 'TRX-LTE-1',
            'status' => 'pending',
        ]);

        // Payment page renders
        $this->get('/lte/payments')->assertOk()->assertSee('TRX-LTE-1');

        // Verify → invoice becomes paid
        $this->post('/lte/payments/'.$payment->id.'/verify')->assertRedirect();
        $this->assertSame('verified', $payment->fresh()->status);
        $this->assertSame('paid', $invoice->fresh()->status);

        // Second payment pending → reject
        $payment2 = Payment::create([
            'invoice_id' => $invoice->id, 'customer_id' => $customer->id,
            'amount' => 500000, 'payment_date' => today(), 'reference_number' => 'TRX-LTE-2',
            'status' => 'pending',
        ]);
        $this->post('/lte/payments/'.$payment2->id.'/reject', ['reason' => 'bukti tidak valid'])->assertRedirect();
        $this->assertSame('rejected', $payment2->fresh()->status);
    }

    public function test_drivers_list_detail_and_toggle_availability(): void
    {
        $this->actingAs($this->owner);

        $driver = Driver::create([
            'name' => 'Andi LTE', 'sim_number' => 'SIM-LTE-1', 'phone' => '08177770001',
            'sim_type' => 'A', 'sim_expiry' => now()->addYear(), 'location_id' => 1,
            'is_active' => true, 'is_available' => true, 'rating' => 4.5, 'total_trips' => 12,
        ]);

        $this->get('/lte/drivers')->assertOk()->assertSee('Andi LTE');
        $this->get('/lte/drivers/'.$driver->id)->assertOk()->assertSee('SIM-LTE-1');

        $this->post('/lte/drivers/'.$driver->id.'/toggle-availability')->assertRedirect();
        $this->assertFalse($driver->fresh()->is_available);

        $this->post('/lte/drivers/'.$driver->id.'/toggle-availability')->assertRedirect();
        $this->assertTrue($driver->fresh()->is_available);
    }

    public function test_deposit_refund_via_order_page(): void
    {
        $this->actingAs($this->owner);

        $vehicle = $this->makeVehicle('Avanza Deposit', 'B 4300 DP');
        $customer = Customer::create([
            'name' => 'Dep Cust', 'email' => 'dep-cust@test.local', 'phone' => '08155550005',
            'customer_type' => 'individual', 'verification_status' => 'verified', 'is_active' => true,
        ]);

        $order = RentalOrder::create([
            'order_number' => 'RO-LTE-DEP-1', 'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id, 'location_id' => 1,
            'start_date' => '2026-10-01', 'end_date' => '2026-10-05',
            'rental_type' => 'self_drive', 'duration_days' => 4,
            'daily_rate_snapshot' => 350000, 'subtotal' => 1400000,
            'final_amount' => 1554000, 'amount_paid' => 1554000, 'balance_due' => 0,
            'deposit_amount' => 500000, 'status' => 'active', 'payment_status' => 'paid',
        ]);

        $deposit = Deposit::create([
            'customer_id' => $customer->id, 'rental_order_id' => $order->id,
            'amount' => 500000, 'deposit_status' => 'held', 'received_at' => now(),
        ]);

        // Refund with deductions → penalty invoice auto-created, deposit refunded
        $this->post('/lte/orders/'.$order->id.'/deposits/'.$deposit->id.'/refund', [
            'fuel' => 50000, 'cleaning' => 25000,
        ])->assertRedirect();

        $this->assertSame('refunded', $deposit->fresh()->deposit_status);
        $this->assertEquals(425000.0, (float) $deposit->fresh()->refund_amount);
        $this->assertDatabaseHas('invoices', ['rental_order_id' => $order->id, 'type' => 'penalty', 'total_amount' => 75000]);
    }
}

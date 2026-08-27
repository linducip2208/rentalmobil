<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\RentalOrder;
use App\Models\Vehicle;
use App\Models\VehicleInspection;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalCustomerOpsTest extends TestCase
{
    use RefreshDatabase;

    private function customer(array $extra = []): Customer
    {
        return Customer::create(array_merge([
            'name' => 'Portal Ops', 'email' => 'pops'.uniqid().'@test.local', 'phone' => '0825',
            'customer_type' => 'individual', 'verification_status' => 'verified',
            'password' => 'password', 'is_active' => true,
        ], $extra));
    }

    private function order(Customer $customer, array $extra = []): RentalOrder
    {
        $vehicle = Vehicle::create(['name' => 'Brio Portal', 'slug' => 'brio-portal-'.uniqid(), 'category_id' => 1, 'brand_id' => 1, 'location_id' => 1, 'plate_number' => 'B '.rand(1000, 9999).' PT', 'year' => 2024, 'color' => 'Merah', 'transmission' => 'automatic', 'seat_count' => 5, 'daily_rate' => 400000, 'weekly_rate' => 2400000, 'monthly_rate' => 8000000, 'deposit_amount' => 400000, 'status' => 'available', 'is_active' => true]);

        return RentalOrder::create(array_merge([
            'customer_id' => $customer->id, 'vehicle_id' => $vehicle->id, 'location_id' => 1,
            'start_date' => now()->addDays(3), 'end_date' => now()->addDays(5),
            'rental_type' => 'self_drive', 'duration_days' => 2, 'daily_rate_snapshot' => 400000,
            'subtotal' => 800000, 'addon_total' => 0, 'discount_total' => 0, 'tax_total' => 88000,
            'final_amount' => 888000, 'amount_paid' => 0, 'balance_due' => 888000,
            'deposit_amount' => 400000, 'status' => 'ready_for_handover',
        ], $extra));
    }

    public function test_customer_can_reschedule_own_order_and_delta_invoiced(): void
    {
        $customer = $this->customer();
        $order = $this->order($customer);

        $this->actingAs($customer, 'customer')
            ->post(route('portal.orders.reschedule', $order), [
                'start_date' => now()->addDays(10)->toDateString(),
                'end_date' => now()->addDays(14)->toDateString(),
            ])
            ->assertRedirect()
            ->assertSessionHas('status');

        $fresh = $order->fresh();
        $this->assertSame(now()->addDays(10)->toDateString(), $fresh->start_date->toDateString());
        $this->assertSame(4, $fresh->duration_days);

        $invoice = Invoice::where('rental_order_id', $order->id)->where('type', 'additional')->first();
        $this->assertNotNull($invoice, 'Selisih kenaikan durasi harus jadi invoice tambahan.');
        $this->assertGreaterThan(0, (float) $invoice->total_amount);
    }

    public function test_reschedule_blocked_when_vehicle_conflicts(): void
    {
        $customer = $this->customer();
        $order = $this->order($customer);

        // Order lain di kendaraan sama menutup rentang baru.
        $otherCustomer = $this->customer();
        RentalOrder::create([
            'customer_id' => $otherCustomer->id, 'vehicle_id' => $order->vehicle_id, 'location_id' => 1,
            'start_date' => now()->addDays(9), 'end_date' => now()->addDays(15),
            'rental_type' => 'self_drive', 'duration_days' => 6, 'daily_rate_snapshot' => 400000,
            'subtotal' => 2400000, 'addon_total' => 0, 'discount_total' => 0, 'tax_total' => 0,
            'final_amount' => 2400000, 'amount_paid' => 0, 'balance_due' => 2400000,
            'deposit_amount' => 0, 'status' => 'ready_for_handover',
        ]);

        $this->actingAs($customer, 'customer')
            ->post(route('portal.orders.reschedule', $order), [
                'start_date' => now()->addDays(10)->toDateString(),
                'end_date' => now()->addDays(14)->toDateString(),
            ])
            ->assertStatus(422);

        $this->assertNotSame(now()->addDays(10)->toDateString(), $order->fresh()->start_date->toDateString());
    }

    public function test_reschedule_other_customers_order_is_404(): void
    {
        $customer = $this->customer();
        $order = $this->order($this->customer());

        $this->actingAs($customer, 'customer')
            ->post(route('portal.orders.reschedule', $order), [
                'start_date' => now()->addDays(10)->toDateString(),
                'end_date' => now()->addDays(14)->toDateString(),
            ])
            ->assertNotFound();
    }

    public function test_inspection_history_visible_to_owner_only(): void
    {
        $customer = $this->customer();
        $order = $this->order($customer);

        VehicleInspection::create([
            'rental_order_id' => $order->id, 'vehicle_id' => $order->vehicle_id,
            'type' => 'checkout', 'checklist' => ['fuel_level' => 'full'],
            'photos' => ['inspections/x.jpg'], 'result' => 'pass', 'inspected_at' => now(),
        ]);

        $this->actingAs($customer, 'customer')->get(route('portal.inspections'))
            ->assertOk()->assertSee('Serah Terima');

        $other = $this->customer();
        $this->actingAs($other, 'customer')->get(route('portal.inspections'))
            ->assertOk()->assertDontSee('Brio Portal');
    }

    public function test_booking_blocked_when_sim_expires_during_rental(): void
    {
        $customer = $this->customer(['sim_expiry_date' => now()->addDays(4)]);
        $vehicle = Vehicle::create(['name' => 'Sim Guard', 'slug' => 'sim-guard-'.uniqid(), 'category_id' => 1, 'brand_id' => 1, 'location_id' => 1, 'plate_number' => 'B '.rand(1000, 9999).' SG', 'year' => 2024, 'color' => 'Putih', 'transmission' => 'automatic', 'seat_count' => 5, 'daily_rate' => 400000, 'weekly_rate' => 2400000, 'monthly_rate' => 8000000, 'deposit_amount' => 400000, 'status' => 'available', 'is_active' => true]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('SIM');

        app(BookingService::class)->createBooking([
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'start_date' => now()->addDays(3)->toDateString(),
            'end_date' => now()->addDays(6)->toDateString(),
            'rental_type' => 'self_drive',
        ]);
    }
}

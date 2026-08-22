<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Deposit;
use App\Models\Driver;
use App\Models\Invoice;
use App\Models\RentalOrder;
use App\Models\TripPermit;
use App\Models\Vehicle;
use App\Services\DepositRefundService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RentalOperationsTest extends TestCase
{
    use RefreshDatabase;

    private function vehicle(array $extra = []): Vehicle
    {
        return Vehicle::create(array_merge([
            'name' => 'Xpander Ops', 'slug' => 'xpander-ops-'.uniqid(), 'category_id' => 1, 'brand_id' => 1,
            'location_id' => 1, 'plate_number' => 'B '.rand(1000, 9999).' OP', 'year' => 2024,
            'color' => 'Abu', 'transmission' => 'automatic', 'seat_count' => 7,
            'daily_rate' => 450000, 'weekly_rate' => 2700000, 'monthly_rate' => 9000000,
            'deposit_amount' => 500000, 'status' => 'available', 'is_active' => true,
        ], $extra));
    }

    private function order(Vehicle $vehicle): RentalOrder
    {
        $customer = Customer::create(['name' => 'Ops User', 'email' => 'ops'.uniqid().'@test.local', 'phone' => '0822', 'customer_type' => 'individual', 'verification_status' => 'verified', 'is_active' => true]);

        return RentalOrder::create([
            'customer_id' => $customer->id, 'vehicle_id' => $vehicle->id, 'location_id' => 1,
            'start_date' => now()->subDay(), 'end_date' => now()->addDay(),
            'rental_type' => 'with_driver', 'duration_days' => 2, 'daily_rate_snapshot' => 450000,
            'subtotal' => 900000, 'addon_total' => 0, 'discount_total' => 0, 'tax_total' => 0,
            'final_amount' => 900000, 'amount_paid' => 0, 'balance_due' => 900000,
            'deposit_amount' => 500000, 'status' => 'active',
        ]);
    }

    public function test_expired_documents_block_booking_availability(): void
    {
        $vehicle = $this->vehicle(['tax_due_date' => now()->addDays(3)]);

        $expired = $vehicle->expiredDocuments(now()->addDays(10));
        $this->assertCount(1, $expired);
        $this->assertStringContainsString('Pajak Tahunan', $expired[0]);

        $valid = $vehicle->hasValidDocumentsUntil(now()->addDay());
        $this->assertTrue($valid);

        // STNK sudah lewat
        $vehicle->update(['stnk_due_date' => now()->subDay()]);
        $this->assertFalse($vehicle->fresh()->hasValidDocumentsUntil(now()));
    }

    public function test_deposit_refund_with_deductions_creates_penalty_invoice(): void
    {
        $order = $this->order($this->vehicle());
        $deposit = Deposit::create([
            'customer_id' => $order->customer_id, 'rental_order_id' => $order->id,
            'amount' => 500000, 'deposit_status' => 'held', 'received_at' => now(),
        ]);

        $result = app(DepositRefundService::class)->refund($deposit, [
            'fuel' => 75000,
            'cleaning' => 50000,
            'damage' => 100000,
        ], userId: 1);

        $this->assertSame('refunded', $result->deposit_status);
        $this->assertSame(275000.0, (float) $result->refund_amount);

        $penalty = Invoice::where('rental_order_id', $order->id)->where('type', 'penalty')->first();
        $this->assertNotNull($penalty);
        $this->assertSame(225000.0, (float) $penalty->total_amount);
        $this->assertStringContainsString('Kurang BBM', $penalty->notes);
    }

    public function test_full_deposit_refund_without_deductions(): void
    {
        $order = $this->order($this->vehicle());
        $deposit = Deposit::create([
            'customer_id' => $order->customer_id, 'rental_order_id' => $order->id,
            'amount' => 500000, 'deposit_status' => 'received', 'received_at' => now(),
        ]);

        $result = app(DepositRefundService::class)->refund($deposit, [], userId: 1);

        $this->assertSame(500000.0, (float) $result->refund_amount);
        $this->assertSame(0, Invoice::where('type', 'penalty')->count());
    }

    public function test_spj_lifecycle_open_close_with_costs(): void
    {
        $vehicle = $this->vehicle();
        $order = $this->order($vehicle);
        $driver = Driver::create(['name' => 'Supir Uji', 'phone' => '082300000001', 'location_id' => 1, 'sim_type' => 'B1', 'sim_number' => 'SIM-777', 'is_available' => true, 'is_active' => true]);

        $permit = TripPermit::create([
            'rental_order_id' => $order->id, 'driver_id' => $driver->id,
            'route_from' => 'Denpasar', 'route_to' => 'Surabaya',
            'fuel_start_level' => 'full', 'odometer_start' => 45100,
            'toll_cost' => 185000, 'parking_cost' => 40000, 'accommodation_cost' => 350000,
            'started_at' => now(),
        ]);

        $this->assertMatchesRegularExpression('/^SPJ-\d{8}-0001$/', $permit->spj_number);
        $this->assertSame(575000.0, $permit->totalOperationalCost());

        $second = TripPermit::create([
            'rental_order_id' => $order->id, 'driver_id' => $driver->id,
            'route_from' => 'A', 'route_to' => 'B',
        ]);
        $this->assertNotSame($permit->spj_number, $second->spj_number);

        $permit->update(['fuel_end_level' => 'quarter', 'odometer_end' => 46950, 'status' => 'closed', 'finished_at' => now()]);

        $this->assertSame('closed', $permit->fresh()->status);
        $this->assertEquals(1850, max(0, $permit->odometer_end - $permit->odometer_start));
    }
}

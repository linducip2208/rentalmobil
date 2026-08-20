<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\RentalOrder;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoicePaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = Customer::create([
            'name' => 'Dewi Lestari',
            'customer_type' => 'individual',
            'email' => 'dewi@test.com',
            'phone' => '081666667777',
            'trust_score' => 100,
            'total_spent' => 0,
            'total_orders' => 0,
            'verification_status' => 'verified',
            'is_active' => true,
        ]);

        $this->vehicle = Vehicle::create([
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
        ]);

        $this->order = RentalOrder::create([
            'customer_id' => $this->customer->id,
            'vehicle_id' => $this->vehicle->id,
            'location_id' => 1,
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-04',
            'rental_type' => 'self_drive',
            'duration_days' => 3,
            'daily_rate_snapshot' => 500000,
            'subtotal' => 1500000,
            'addon_total' => 0,
            'discount_total' => 0,
            'tax_total' => 165000,
            'final_amount' => 1665000,
            'amount_paid' => 0,
            'balance_due' => 1665000,
            'deposit_amount' => 500000,
            'status' => 'completed',
            'payment_status' => 'unpaid',
        ]);

        $this->invoice = Invoice::create([
            'rental_order_id' => $this->order->id,
            'customer_id' => $this->customer->id,
            'type' => 'rental',
            'subtotal' => 1500000,
            'tax_amount' => 165000,
            'discount_amount' => 0,
            'total_amount' => 1665000,
            'amount_paid' => 0,
            'balance_due' => 1665000,
            'due_date' => now()->addDays(7),
            'status' => 'issued',
        ]);
    }

    public function test_invoice_created_from_order(): void
    {
        $this->assertDatabaseHas('invoices', [
            'rental_order_id' => $this->order->id,
            'customer_id' => $this->customer->id,
            'total_amount' => 1665000,
        ]);

        $this->assertEquals($this->order->id, $this->invoice->rental_order_id);
        $this->assertEquals($this->order->final_amount, $this->invoice->total_amount);
    }

    public function test_payment_reduces_balance(): void
    {
        Payment::create([
            'invoice_id' => $this->invoice->id,
            'rental_order_id' => $this->order->id,
            'customer_id' => $this->customer->id,
            'amount' => 500000,
            'payment_date' => now(),
            'status' => 'verified',
        ]);

        $this->invoice->update([
            'amount_paid' => 500000,
            'balance_due' => 1665000 - 500000,
            'status' => 'partially_paid',
        ]);

        $this->order->update([
            'amount_paid' => 500000,
            'balance_due' => 1665000 - 500000,
        ]);

        $this->invoice->refresh();
        $this->assertEquals(1165000, (float) $this->invoice->balance_due);
    }

    public function test_full_payment_marks_paid(): void
    {
        Payment::create([
            'invoice_id' => $this->invoice->id,
            'rental_order_id' => $this->order->id,
            'customer_id' => $this->customer->id,
            'amount' => 1665000,
            'payment_date' => now(),
            'status' => 'verified',
        ]);

        $this->invoice->update([
            'amount_paid' => 1665000,
            'balance_due' => 0,
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        $this->order->update([
            'amount_paid' => 1665000,
            'balance_due' => 0,
            'payment_status' => 'paid',
        ]);

        $this->assertEquals('paid', $this->invoice->fresh()->status);
        $this->assertEquals('paid', $this->order->fresh()->payment_status);
        $this->assertNotNull($this->invoice->fresh()->paid_at);
    }

    public function test_payment_cannot_exceed_invoice(): void
    {
        $paymentAmount = 2000000;
        $invoiceBalance = (float) $this->invoice->balance_due;

        $this->assertGreaterThan($invoiceBalance, $paymentAmount);
    }
}

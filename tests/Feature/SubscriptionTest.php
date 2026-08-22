<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Subscription;
use App\Models\Vehicle;
use App\Services\SubscriptionBillingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionTest extends TestCase
{
    use RefreshDatabase;

    private function subscription(array $extra = []): Subscription
    {
        $customer = Customer::create(['name' => 'Langganan User', 'email' => 'subs'.uniqid().'@test.local', 'phone' => '0818', 'customer_type' => 'individual', 'verification_status' => 'verified', 'is_active' => true]);
        $plate = 'B '.rand(1000, 9999).' SB';
        $vehicle = Vehicle::create(['name' => 'Bimu Langganan', 'slug' => 'bimu-langganan-'.uniqid(), 'category_id' => 1, 'brand_id' => 1, 'location_id' => 1, 'plate_number' => $plate, 'year' => 2024, 'color' => 'Hitam', 'transmission' => 'automatic', 'seat_count' => 7, 'daily_rate' => 500000, 'weekly_rate' => 3000000, 'monthly_rate' => 10000000, 'deposit_amount' => 500000, 'status' => 'available', 'is_active' => true]);

        return Subscription::create(array_merge([
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'plan_name' => 'Paket Bulanan',
            'billing_cycle' => 'monthly',
            'price_per_cycle' => 10000000,
            'start_date' => now()->toDateString(),
            'auto_renew' => true,
            'status' => 'active',
        ], $extra));
    }

    public function test_generate_invoice_creates_invoice_and_advances_period(): void
    {
        $subscription = $this->subscription();

        $invoice = app(SubscriptionBillingService::class)->generateInvoice($subscription);

        $this->assertSame(10000000.0, (float) $invoice->total_amount);
        $this->assertSame('additional', $invoice->type);
        $this->assertSame('issued', $invoice->status);
        $this->assertTrue($subscription->fresh()->current_period_end->isNextMonth());
        $this->assertStringContainsString('Paket Bulanan', $invoice->notes);
    }

    public function test_run_billing_only_bills_due_subscriptions(): void
    {
        $due = $this->subscription(['current_period_end' => now()->subDay()]);
        $notDue = $this->subscription(['current_period_end' => now()->addDays(20)]);
        $paused = $this->subscription(['status' => 'paused']);

        $count = app(SubscriptionBillingService::class)->runBilling();

        $this->assertSame(1, $count);
        $this->assertDatabaseHas('subscriptions', ['id' => $notDue->id, 'current_period_end' => $notDue->current_period_end->toDateString()]);
    }

    public function test_cancel_stops_billing(): void
    {
        $subscription = $this->subscription();
        app(SubscriptionBillingService::class)->cancel($subscription, 'tidak dipakai');

        $this->assertSame('cancelled', $subscription->fresh()->status);
        $this->assertFalse($subscription->fresh()->auto_renew);
        $this->assertSame(0, app(SubscriptionBillingService::class)->runBilling());
    }
}

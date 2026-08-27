<?php

namespace Tests\Feature;

use App\Models\CorporateAccount;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\RentalOrder;
use App\Models\Vehicle;
use App\Services\CorporateBillingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CorporateBillingTest extends TestCase
{
    use RefreshDatabase;

    private function account(float $limit = 10000000): CorporateAccount
    {
        return CorporateAccount::create(['name' => 'PT Maju Jaya', 'credit_limit' => $limit, 'payment_terms_days' => 30, 'is_active' => true]);
    }

    private function customer(CorporateAccount $account): Customer
    {
        return Customer::create([
            'name' => 'Driver PT '.uniqid(),
            'email' => 'corp'.uniqid().'@test.local',
            'phone' => '0820',
            'customer_type' => 'corporate',
            'corporate_account_id' => $account->id,
            'verification_status' => 'verified',
            'is_active' => true,
        ]);
    }

    public function test_outstanding_and_available_credit(): void
    {
        $account = $this->account(10000000);
        $customer = $this->customer($account);

        Invoice::create(['customer_id' => $customer->id, 'type' => 'rental', 'subtotal' => 4000000, 'total_amount' => 4000000, 'balance_due' => 4000000, 'status' => 'issued']);

        $this->assertSame(4000000.0, $account->outstandingBalance());
        $this->assertSame(6000000.0, $account->availableCredit());

        Invoice::create(['customer_id' => $customer->id, 'type' => 'rental', 'subtotal' => 1000000, 'total_amount' => 1000000, 'balance_due' => 1000000, 'status' => 'paid']);

        $this->assertSame(4000000.0, $account->fresh()->outstandingBalance(), 'Invoice paid tidak dihitung outstanding.');
    }

    public function test_credit_limit_check_blocks_over_limit(): void
    {
        $service = app(CorporateBillingService::class);
        $account = $this->account(5000000);
        $customer = $this->customer($account);

        Invoice::create(['customer_id' => $customer->id, 'type' => 'rental', 'subtotal' => 4500000, 'total_amount' => 4500000, 'balance_due' => 4500000, 'status' => 'issued']);

        $ok = $service->checkCreditLimit($account, 400000);
        $this->assertTrue($ok['allowed']);

        $blocked = $service->checkCreditLimit($account, 600000);
        $this->assertFalse($blocked['allowed']);
    }

    public function test_statement_rows_aggregate_account_orders(): void
    {
        $service = app(CorporateBillingService::class);
        $account = $this->account();
        $c1 = $this->customer($account);
        $c2 = $this->customer($account);

        $vehicle = Vehicle::create(['name' => 'Innova Korporat', 'slug' => 'innova-korporat-'.uniqid(), 'category_id' => 1, 'brand_id' => 1, 'location_id' => 1, 'plate_number' => 'B '.rand(1000, 9999).' KP', 'year' => 2024, 'color' => 'Silver', 'transmission' => 'automatic', 'seat_count' => 7, 'daily_rate' => 500000, 'weekly_rate' => 3000000, 'monthly_rate' => 10000000, 'deposit_amount' => 500000, 'status' => 'available', 'is_active' => true]);

        foreach ([$c1, $c2] as $i => $c) {
            $invoice = Invoice::create(['customer_id' => $c->id, 'type' => 'rental', 'subtotal' => 1000000 + $i, 'total_amount' => 1000000 + $i, 'balance_due' => 1000000 + $i, 'status' => 'issued']);
            RentalOrder::create([
                'customer_id' => $c->id, 'vehicle_id' => $vehicle->id, 'location_id' => 1,
                'start_date' => now()->subDays(5), 'end_date' => now()->subDays(3),
                'rental_type' => 'corporate', 'duration_days' => 2, 'daily_rate_snapshot' => 500000,
                'subtotal' => 1000000, 'addon_total' => 0, 'discount_total' => 0, 'tax_total' => 0,
                'final_amount' => 1000000, 'amount_paid' => 0, 'balance_due' => 1000000,
                'deposit_amount' => 0, 'status' => 'completed', 'purchase_order_number' => 'PO-TEST-'.$i,
                'created_at' => now(),
            ]);
            $invoice->update(['rental_order_id' => RentalOrder::where('customer_id', $c->id)->first()->id]);
        }

        $rows = $service->statementRows($account, now()->subDays(7), now());

        $this->assertCount(2, $rows);
        $this->assertEquals(2000001.0, $rows->sum('invoiced'));
        $pdf = $service->generateStatementPdf($account, now()->subDays(7), now());
        $this->assertGreaterThan(1000, strlen($pdf->output()));
    }
}

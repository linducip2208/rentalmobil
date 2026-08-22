<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\PromoVoucher;
use App\Models\RentalOrder;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketingAutomationTest extends TestCase
{
    use RefreshDatabase;

    private function vehicle(): Vehicle
    {
        return Vehicle::create(['name' => 'Mkt Car', 'slug' => 'mkt-car-'.uniqid(), 'category_id' => 1, 'brand_id' => 1, 'location_id' => 1, 'plate_number' => 'B '.rand(1000, 9999).' MK', 'year' => 2024, 'color' => 'Putih', 'transmission' => 'automatic', 'seat_count' => 5, 'daily_rate' => 400000, 'weekly_rate' => 2400000, 'monthly_rate' => 8000000, 'deposit_amount' => 400000, 'status' => 'available', 'is_active' => true]);
    }

    private function customer(array $extra = []): Customer
    {
        return Customer::create(array_merge(['name' => 'Mkt User', 'email' => 'mkt'.uniqid().'@test.local', 'phone' => '0826', 'customer_type' => 'individual', 'verification_status' => 'verified', 'is_active' => true], $extra));
    }

    public function test_winback_voucher_created_for_dormant_customer(): void
    {
        $customer = $this->customer();
        $order = RentalOrder::create([
            'customer_id' => $customer->id, 'vehicle_id' => $this->vehicle()->id, 'location_id' => 1,
            'start_date' => now()->subDays(120), 'end_date' => now()->subDays(118),
            'rental_type' => 'self_drive', 'duration_days' => 2, 'daily_rate_snapshot' => 400000,
            'subtotal' => 800000, 'addon_total' => 0, 'discount_total' => 0, 'tax_total' => 0,
            'final_amount' => 800000, 'amount_paid' => 800000, 'balance_due' => 0,
            'deposit_amount' => 0, 'status' => 'completed',
        ]);

        $this->artisan('marketing:generate-vouchers')->assertSuccessful();

        $voucher = PromoVoucher::where('code', 'WINBACK-'.$customer->id.'-'.now()->format('Ym'))->first();
        $this->assertNotNull($voucher);
        $this->assertSame(1, $voucher->usage_limit);

        // Idempoten: jalan lagi tidak duplikat.
        $this->artisan('marketing:generate-vouchers')->assertSuccessful();
        $this->assertSame(1, PromoVoucher::where('code', 'like', "WINBACK-{$customer->id}-%")->count());
    }

    public function test_active_customer_gets_no_winback(): void
    {
        $customer = $this->customer();
        RentalOrder::create([
            'customer_id' => $customer->id, 'vehicle_id' => $this->vehicle()->id, 'location_id' => 1,
            'start_date' => now()->subDays(5), 'end_date' => now()->subDays(3),
            'rental_type' => 'self_drive', 'duration_days' => 2, 'daily_rate_snapshot' => 400000,
            'subtotal' => 800000, 'addon_total' => 0, 'discount_total' => 0, 'tax_total' => 0,
            'final_amount' => 800000, 'amount_paid' => 800000, 'balance_due' => 0,
            'deposit_amount' => 0, 'status' => 'completed',
        ]);

        $this->artisan('marketing:generate-vouchers')->assertSuccessful();

        $this->assertSame(0, PromoVoucher::where('code', 'like', "WINBACK-{$customer->id}-%")->count());
    }

    public function test_birthday_voucher_created_on_birthdate(): void
    {
        $birthdayCustomer = $this->customer(['date_of_birth' => now()->subYears(30)]);
        $other = $this->customer(['date_of_birth' => now()->subYears(30)->subDays(10)]);

        $this->artisan('marketing:generate-vouchers')->assertSuccessful();

        $this->assertNotNull(PromoVoucher::where('code', 'BDAY-'.$birthdayCustomer->id.'-'.now()->format('Ym'))->first());
        $this->assertNull(PromoVoucher::where('code', 'BDAY-'.$other->id.'-'.now()->format('Ym'))->first());
    }

    public function test_review_request_dispatched_for_yesterday_returns(): void
    {
        $provider = \App\Models\Provider::create(['name' => 'WA Uji', 'type' => 'whatsapp', 'api_format' => 'rest_json', 'base_url' => 'https://wa.test/send', 'is_active' => true]);
        \App\Models\NotificationTemplate::create([
            'provider_id' => $provider->id,
            'name' => 'Minta Review',
            'event_type' => 'review_request',
            'channel' => 'whatsapp',
            'body' => 'Halo {{customer_name}}, bagaimana pengalaman sewa {{vehicle_name}}?',
            'is_active' => true,
        ]);

        $customer = $this->customer();
        $order = RentalOrder::create([
            'customer_id' => $customer->id, 'vehicle_id' => $this->vehicle()->id, 'location_id' => 1,
            'start_date' => now()->subDays(3), 'end_date' => now()->subDay(),
            'actual_return_date' => now()->subDay(),
            'rental_type' => 'self_drive', 'duration_days' => 2, 'daily_rate_snapshot' => 400000,
            'subtotal' => 800000, 'addon_total' => 0, 'discount_total' => 0, 'tax_total' => 0,
            'final_amount' => 800000, 'amount_paid' => 800000, 'balance_due' => 0,
            'deposit_amount' => 0, 'status' => 'completed',
        ]);

        $this->artisan('marketing:request-reviews')->assertSuccessful();

        $this->assertDatabaseHas('notification_queues', [
            'notifiable_type' => Customer::class,
            'notifiable_id' => $customer->id,
            'event_type' => 'review_request',
        ]);
    }
}

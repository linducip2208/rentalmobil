<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\NotificationQueue;
use App\Models\NotificationTemplate;
use App\Models\Provider;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Webhook;
use App\Models\WebhookDelivery;
use App\Services\BookingService;
use App\Services\WebhookDispatchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FinanceSystemOpsTest extends TestCase
{
    use RefreshDatabase;

    public function test_webhook_dispatch_signs_payload_and_delivers(): void
    {
        Http::fake(['https://partner.test/hook' => Http::response(['ok' => true], 200)]);

        Webhook::create([
            'name' => 'Partner A', 'url' => 'https://partner.test/hook', 'secret' => 'rahasia-partner',
            'events' => ['booking.created'], 'is_active' => true,
        ]);

        $count = app(WebhookDispatchService::class)->dispatch('booking.created', ['booking_number' => 'BKG-1']);

        $this->assertSame(1, $count);
        $delivery = WebhookDelivery::first();
        $this->assertSame('delivered', $delivery->status);
        $this->assertSame(200, $delivery->response_code);

        Http::assertSent(function ($request) {
            $expected = hash_hmac('sha256', json_encode([
                'event' => 'booking.created',
                'sent_at' => $request['sent_at'],
                'data' => ['booking_number' => 'BKG-1'],
            ]), 'rahasia-partner');

            return $request->header('X-Webhook-Signature')[0] === $expected
                && $request->header('X-Webhook-Event')[0] === 'booking.created';
        });
    }

    public function test_webhook_failure_marks_pending_for_retry(): void
    {
        Http::fake([
            'https://down.test/hook' => Http::sequence()
                ->push('err', 500)
                ->push(['ok' => true], 200),
        ]);

        Webhook::create([
            'name' => 'Down', 'url' => 'https://down.test/hook', 'secret' => 's',
            'events' => ['payment.paid'], 'is_active' => true,
        ]);

        app(WebhookDispatchService::class)->dispatch('payment.paid', ['tx' => 1]);

        $delivery = WebhookDelivery::first();
        $this->assertSame('pending', $delivery->status);
        $this->assertNotNull($delivery->next_retry_at);

        // Retry dengan server pulih (response kedua pada sequence).
        app(WebhookDispatchService::class)->retryFailed();

        $this->assertSame('delivered', $delivery->fresh()->status);
    }

    public function test_booking_created_fires_webhook_event(): void
    {
        Http::fake();
        Webhook::create(['name' => 'W', 'url' => 'https://w.test/x', 'secret' => 's', 'events' => ['booking.created'], 'is_active' => true]);

        $customer = Customer::create(['name' => 'Hook User', 'email' => 'hk'.uniqid().'@test.local', 'phone' => '0827', 'customer_type' => 'individual', 'verification_status' => 'verified', 'is_active' => true]);
        $vehicle = Vehicle::create(['name' => 'Hook Car', 'slug' => 'hook-car-'.uniqid(), 'category_id' => 1, 'brand_id' => 1, 'location_id' => 1, 'plate_number' => 'B '.rand(1000, 9999).' HK', 'year' => 2024, 'color' => 'Putih', 'transmission' => 'automatic', 'seat_count' => 5, 'daily_rate' => 300000, 'weekly_rate' => 1800000, 'monthly_rate' => 6000000, 'deposit_amount' => 300000, 'status' => 'available', 'is_active' => true]);

        app(BookingService::class)->createBooking([
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'start_date' => now()->addDays(5)->toDateString(),
            'end_date' => now()->addDays(7)->toDateString(),
            'rental_type' => 'self_drive',
            'source' => 'admin',
        ]);

        Http::assertSent(fn ($r) => str_contains($r->url(), 'w.test') && ($r['event'] ?? '') === 'booking.created');
    }

    public function test_dunning_command_sends_for_overdue_invoice(): void
    {
        $provider = Provider::create(['name' => 'WA Dunning', 'type' => 'whatsapp', 'api_format' => 'rest_json', 'base_url' => 'https://wa.test/send', 'is_active' => true]);
        NotificationTemplate::create(['provider_id' => $provider->id, 'name' => 'Dunning', 'event_type' => 'payment_dunning', 'channel' => 'whatsapp', 'body' => 'INV {{invoice_number}} telat {{days_late}} hari. {{urgency_message}}', 'is_active' => true]);

        $customer = Customer::create(['name' => 'Telat Bayar', 'email' => 'dn'.uniqid().'@test.local', 'phone' => '0828', 'customer_type' => 'individual', 'verification_status' => 'verified', 'is_active' => true]);
        Invoice::create(['customer_id' => $customer->id, 'type' => 'rental', 'subtotal' => 1000000, 'total_amount' => 1000000, 'balance_due' => 1000000, 'due_date' => now()->subDays(10), 'status' => 'issued']);

        $this->artisan('finance:remind-overdue')->assertSuccessful();

        $this->assertDatabaseHas('notification_queues', [
            'notifiable_id' => $customer->id,
            'event_type' => 'payment_dunning',
        ]);

        // Dedup: jalan lagi dalam 3 hari tidak kirim ulang.
        $this->artisan('finance:remind-overdue')->assertSuccessful();
        $this->assertSame(1, NotificationQueue::where('event_type', 'payment_dunning')->where('notifiable_id', $customer->id)->count());
    }

    public function test_daily_owner_report_dispatches_to_admin_users(): void
    {
        $provider = Provider::create(['name' => 'WA Report', 'type' => 'whatsapp', 'api_format' => 'rest_json', 'base_url' => 'https://wa.test/send', 'is_active' => true]);
        NotificationTemplate::create(['provider_id' => $provider->id, 'name' => 'Report Harian', 'event_type' => 'daily_owner_report', 'channel' => 'whatsapp', 'body' => 'Laporan {{report_date}}: booking baru {{new_bookings}}', 'is_active' => true]);

        $owner = User::find(1); // di-seed TestCase

        $this->artisan('report:daily-owner', ['--date' => now()->subDay()->toDateString()])->assertSuccessful();

        $this->assertDatabaseHas('notification_queues', [
            'notifiable_id' => $owner->id,
            'event_type' => 'daily_owner_report',
        ]);
    }
}

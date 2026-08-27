<?php

namespace Tests\Feature;

use App\Models\GpsIntegration;
use App\Models\GpsTracker;
use App\Models\Provider;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\Gps\GpsCommandService;
use App\Services\Gps\GpsPositionIngestor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GpsByokIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_byok_secrets_are_encrypted_at_rest(): void
    {
        $integration = $this->integration();
        $integration->credential_secret = 'api-key-rahasia';
        $integration->webhook_secret = 'webhook-rahasia';
        $integration->save();

        $raw = \DB::table('gps_integrations')->where('id', $integration->id)->first();
        $this->assertNotSame('api-key-rahasia', $raw->credential_secret_encrypted);
        $this->assertNotSame('webhook-rahasia', $raw->webhook_secret_encrypted);
        $this->assertSame('api-key-rahasia', $integration->fresh()->credential_secret);
        $this->assertSame('webhook-rahasia', $integration->fresh()->webhook_secret);
    }

    public function test_configurable_mapping_ingests_provider_payload(): void
    {
        $integration = $this->integration([
            'field_mapping' => [
                'device_id' => 'unit.code', 'latitude' => 'position.lat', 'longitude' => 'position.lng',
                'speed' => 'telemetry.velocity', 'heading' => 'telemetry.bearing',
                'recorded_at' => 'timestamps.gps', 'battery_level' => 'telemetry.power',
            ],
        ]);
        $vehicle = $this->vehicle();
        $tracker = GpsTracker::create([
            'gps_integration_id' => $integration->id, 'vehicle_id' => $vehicle->id,
            'device_id' => 'local-001', 'external_device_id' => 'provider-991', 'status' => 'active', 'is_active' => true,
        ]);

        $log = app(GpsPositionIngestor::class)->ingest($integration, [
            'unit' => ['code' => 'provider-991'],
            'position' => ['lat' => -6.2, 'lng' => 106.8],
            'telemetry' => ['velocity' => 42.5, 'bearing' => 180, 'power' => 77],
            'timestamps' => ['gps' => '2026-08-21T03:00:00+07:00'],
        ]);

        $this->assertNotNull($log);
        $this->assertSame($vehicle->id, $log->vehicle_id);
        $this->assertEquals(-6.2, $tracker->fresh()->last_latitude);
        $this->assertEquals(42.5, $tracker->fresh()->last_speed);
        $this->assertSame(77, $tracker->fresh()->last_battery_level);
    }

    public function test_duplicate_payload_is_idempotent_and_overspeed_creates_persistent_alert(): void
    {
        $integration = $this->integration(['field_mapping' => ['device_id' => 'id', 'latitude' => 'lat', 'longitude' => 'lng', 'speed' => 'speed']]);
        $tracker = GpsTracker::create(['gps_integration_id' => $integration->id, 'vehicle_id' => $this->vehicle()->id, 'device_id' => 'local-002', 'external_device_id' => 'external-2', 'status' => 'active', 'is_active' => true, 'speed_limit_kmh' => 60]);
        $payload = ['id' => 'external-2', 'lat' => -6.2, 'lng' => 106.8, 'speed' => 88];
        $this->assertNotNull(app(GpsPositionIngestor::class)->ingest($integration, $payload));
        $this->assertNull(app(GpsPositionIngestor::class)->ingest($integration, $payload));
        $this->assertDatabaseCount('gps_logs', 1);
        $this->assertDatabaseHas('gps_alerts', ['gps_tracker_id' => $tracker->id, 'type' => 'overspeed', 'severity' => 'critical']);
    }

    public function test_gps_command_requires_separate_approver_and_uses_dynamic_endpoint(): void
    {
        Http::fake(['https://gps.invalid/*' => Http::response(['accepted' => true])]);
        $integration = $this->integration(['adapter_format' => 'rest_polling', 'commands_endpoint' => 'commands/send']);
        $tracker = GpsTracker::create(['gps_integration_id' => $integration->id, 'vehicle_id' => $this->vehicle()->id, 'device_id' => 'local-003', 'external_device_id' => 'external-3', 'status' => 'active', 'is_active' => true]);
        $requester = User::factory()->create(['role' => 'admin']);
        $approver = User::factory()->create(['role' => 'manager']);
        $service = app(GpsCommandService::class);
        $command = $service->request($tracker, $requester, 'custom_vendor_command', ['mode' => 'safe'], 'Kendaraan dilaporkan hilang');
        $this->expectException(\RuntimeException::class);
        $service->approve($command, $requester);
    }

    public function test_approved_gps_command_can_be_sent(): void
    {
        Http::fake(['https://gps.invalid/*' => Http::response(['accepted' => true])]);
        $integration = $this->integration(['adapter_format' => 'rest_polling', 'commands_endpoint' => 'commands/send']);
        $tracker = GpsTracker::create(['gps_integration_id' => $integration->id, 'vehicle_id' => $this->vehicle()->id, 'device_id' => 'local-004', 'external_device_id' => 'external-4', 'status' => 'active', 'is_active' => true]);
        $requester = User::factory()->create(['role' => 'admin']);
        $approver = User::factory()->create(['role' => 'manager']);
        $service = app(GpsCommandService::class);
        $command = $service->approve($service->request($tracker, $requester, 'vendor_defined', [], 'Tes aman'), $approver);
        $sent = $service->send($command);
        $this->assertSame('sent', $sent->status);
        Http::assertSent(fn ($request) => $request->url() === 'https://gps.invalid/commands/send' && $request['device_id'] === 'external-4');
    }

    private function integration(array $overrides = []): GpsIntegration
    {
        $provider = Provider::create(['name' => 'Provider milik user', 'type' => 'gps', 'api_format' => 'json', 'base_url' => 'https://gps.invalid', 'is_active' => true]);

        return GpsIntegration::create(array_merge([
            'provider_id' => $provider->id, 'adapter_format' => 'webhook_json', 'auth_type' => 'none',
            'field_mapping' => ['device_id' => 'id', 'latitude' => 'lat', 'longitude' => 'lng'], 'is_active' => true,
        ], $overrides));
    }

    private function vehicle(): Vehicle
    {
        return Vehicle::create([
            'name' => 'Mobil GPS Test', 'slug' => 'mobil-gps-test', 'category_id' => 1, 'brand_id' => 1, 'location_id' => 1,
            'plate_number' => 'B 9090 GPS', 'year' => 2025, 'transmission' => 'automatic', 'seat_count' => 5, 'daily_rate' => 500000,
            'weekly_rate' => 3000000, 'monthly_rate' => 10000000, 'late_fee_per_hour' => 25000, 'late_fee_per_day' => 200000, 'deposit_amount' => 500000,
            'status' => 'available', 'is_active' => true,
        ]);
    }
}

<?php

namespace Tests\Feature;

use App\Models\DamageReport;
use App\Models\Provider;
use App\Models\VehicleInspection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AiDamageDetectionTest extends TestCase
{
    use RefreshDatabase;

    private function provider(): Provider
    {
        return Provider::create([
            'name' => 'Vision AI Uji',
            'type' => 'ai',
            'api_format' => 'openai_compatible',
            'base_url' => 'https://ai.test/v1',
            'api_key' => 'sk-test',
            'config' => ['model' => 'vision-model-x'],
            'is_active' => true,
        ]);
    }

    private function inspection(): VehicleInspection
    {
        Storage::fake('public');
        Storage::disk('public')->put('inspections/test.jpg', UploadedFile::fake()->image('test.jpg')->getContent());

        $customer = \App\Models\Customer::create(['name' => 'Inspeksi User', 'email' => 'insp@test.local', 'phone' => '0815', 'customer_type' => 'individual', 'verification_status' => 'verified', 'is_active' => true]);
        $vehicle = \App\Models\Vehicle::create(['name' => 'Avanza Uji', 'slug' => 'avanza-uji', 'category_id' => 1, 'brand_id' => 1, 'location_id' => 1, 'plate_number' => 'B 7000 AI', 'year' => 2024, 'color' => 'Putih', 'transmission' => 'automatic', 'seat_count' => 7, 'daily_rate' => 500000, 'weekly_rate' => 3000000, 'monthly_rate' => 10000000, 'deposit_amount' => 500000, 'status' => 'available', 'is_active' => true]);
        $order = \App\Models\RentalOrder::create(['customer_id' => $customer->id, 'vehicle_id' => $vehicle->id, 'location_id' => 1, 'start_date' => now()->subDays(2), 'end_date' => now(), 'rental_type' => 'self_drive', 'duration_days' => 2, 'daily_rate_snapshot' => 500000, 'subtotal' => 1000000, 'addon_total' => 0, 'discount_total' => 0, 'tax_total' => 110000, 'final_amount' => 1110000, 'amount_paid' => 0, 'balance_due' => 1110000, 'deposit_amount' => 500000, 'status' => 'active']);

        return VehicleInspection::create([
            'rental_order_id' => $order->id,
            'vehicle_id' => $vehicle->id,
            'type' => 'checkin',
            'checklist' => ['ban' => 'ok'],
            'photos' => ['inspections/test.jpg'],
            'result' => 'pass',
            'inspected_at' => now(),
        ]);
    }

    public function test_analyze_stores_findings_and_status_done(): void
    {
        $this->provider();
        $inspection = $this->inspection();

        $findings = [
            ['location_on_vehicle' => 'pintu depan kiri', 'damage_type' => 'scratch', 'severity' => 'moderate', 'description' => 'Baret memanjang 15cm', 'estimated_cost_idr' => 250000, 'confidence' => 0.9],
        ];

        Http::fake([
            'https://ai.test/v1/chat/completions' => Http::response([
                'choices' => [['message' => ['content' => json_encode($findings)]]],
            ], 200),
        ]);

        $result = app(\App\Services\DamageDetectionService::class)->analyze($inspection);

        $this->assertSame('done', $result->ai_status);
        $this->assertSame('pintu depan kiri', $result->ai_analysis[0]['location_on_vehicle']);
        $this->assertNotNull($result->ai_analyzed_at);

        Http::assertSent(fn ($req) => str_contains($req->data()['messages'][0]['content'][1]['image_url']['url'], 'data:image'));
    }

    public function test_draft_reports_created_from_ai_findings(): void
    {
        $this->actingAs(\App\Models\User::find(1));
        $this->provider();

        $inspection = $this->inspection();
        $inspection->update([
            'ai_status' => 'done',
            'ai_analysis' => [
                ['location_on_vehicle' => 'bumper depan', 'damage_type' => 'dent', 'severity' => 'major', 'description' => 'Penyok', 'estimated_cost_idr' => 1500000, 'confidence' => 0.92],
                ['raw_text' => 'respons tidak terstruktur'],
            ],
        ]);

        $created = app(\App\Services\DamageDetectionService::class)->createDraftReportsFromAnalysis($inspection);

        $this->assertSame(1, $created);
        $report = DamageReport::first();
        $this->assertSame('dent', $report->damage_type);
        $this->assertSame('major', $report->severity);
        $this->assertSame('reported', $report->status);
        $this->assertStringStartsWith('[AI]', $report->description);
    }

    public function test_analyze_requires_active_provider_and_photos(): void
    {
        $inspection = $this->inspection();

        $this->expectException(\RuntimeException::class);
        app(\App\Services\DamageDetectionService::class)->analyze($inspection);
    }
}

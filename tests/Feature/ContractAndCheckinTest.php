<?php

namespace Tests\Feature;

use App\Models\Contract;
use App\Models\Customer;
use App\Models\RentalOrder;
use App\Models\Vehicle;
use App\Services\HandoverLinkService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ContractAndCheckinTest extends TestCase
{
    use RefreshDatabase;

    private function order(): RentalOrder
    {
        $customer = Customer::create(['name' => 'Penandatangan', 'email' => 'tte'.uniqid().'@test.local', 'phone' => '0819', 'customer_type' => 'individual', 'verification_status' => 'verified', 'is_active' => true]);
        $vehicle = Vehicle::create(['name' => 'Avanza TTE', 'slug' => 'avanza-tte-'.uniqid(), 'category_id' => 1, 'brand_id' => 1, 'location_id' => 1, 'plate_number' => 'B '.rand(1000, 9999).' TT', 'year' => 2024, 'color' => 'Putih', 'transmission' => 'automatic', 'seat_count' => 7, 'daily_rate' => 500000, 'weekly_rate' => 3000000, 'monthly_rate' => 10000000, 'deposit_amount' => 500000, 'status' => 'available', 'is_active' => true]);

        return RentalOrder::create([
            'customer_id' => $customer->id, 'vehicle_id' => $vehicle->id, 'location_id' => 1,
            'start_date' => now()->addDay(), 'end_date' => now()->addDays(3),
            'rental_type' => 'self_drive', 'duration_days' => 2, 'daily_rate_snapshot' => 500000,
            'subtotal' => 1000000, 'addon_total' => 0, 'discount_total' => 0,
            'tax_total' => 110000, 'final_amount' => 1110000, 'amount_paid' => 0, 'balance_due' => 1110000,
            'deposit_amount' => 500000, 'status' => 'ready_for_handover',
        ]);
    }

    private function fakeSignature(): string
    {
        return 'data:image/png;base64,'.base64_encode(UploadedFile::fake()->image('sig.png')->getContent());
    }

    public function test_contract_signing_full_flow(): void
    {
        Storage::fake('public');
        $order = $this->order();
        $contract = Contract::create([
            'rental_order_id' => $order->id, 'customer_id' => $order->customer_id, 'vehicle_id' => $order->vehicle_id,
            'start_date' => $order->start_date, 'end_date' => $order->end_date,
            'daily_rate' => 500000,
            'total_amount' => 1110000, 'deposit_amount' => 500000, 'status' => 'draft',
        ]);

        $service = app(HandoverLinkService::class);
        $url = $service->issueContractSigning($contract);
        $raw = last(explode('/', parse_url($url, PHP_URL_PATH)));

        $this->get("/handover/kontrak/{$raw}")
            ->assertOk()
            ->assertSee($contract->contract_number);

        $this->post(route('handover.contract.sign', $raw), ['signature' => $this->fakeSignature()])
            ->assertOk();

        $fresh = $contract->fresh();
        $this->assertSame('signed', $fresh->status);
        $this->assertNotNull($fresh->document_hash);
        $this->assertNotNull($fresh->signed_at);
        Storage::disk('public')->assertExists($fresh->customer_signature_url);

        $this->post(route('handover.contract.sign', $raw), ['signature' => $this->fakeSignature()])->assertStatus(410);
    }

    public function test_self_checkin_creates_inspection_and_checks_out_order(): void
    {
        Storage::fake('public');
        $order = $this->order();

        $service = app(HandoverLinkService::class);
        $url = $service->issueCheckIn($order);
        $raw = last(explode('/', parse_url($url, PHP_URL_PATH)));

        $this->get("/handover/checkin/{$raw}")->assertOk()->assertSee($order->vehicle->plate_number);

        $this->post(route('handover.checkin.submit', $raw), [
            'fuel_level' => 'full',
            'odometer_km' => 45230,
            'photos' => [UploadedFile::fake()->image('depan.jpg'), UploadedFile::fake()->image('belakang.jpg')],
        ])->assertOk();

        $inspection = \App\Models\VehicleInspection::where('rental_order_id', $order->id)->first();
        $this->assertNotNull($inspection);
        $this->assertSame('checkout', $inspection->type);
        $this->assertCount(2, $inspection->photos);
        foreach ($inspection->photos as $photo) {
            Storage::disk('public')->assertExists($photo);
        }
        $this->assertSame('checked_out', $order->fresh()->status);
    }

    public function test_invalid_token_returns_404(): void
    {
        $this->get('/handover/kontrak/token-ngawur')->assertNotFound();
        $this->get('/handover/checkin/token-ngawur')->assertNotFound();
    }
}

<?php

namespace Tests\Feature;

use App\Models\PromoVoucher;
use App\Models\SystemSetting;
use App\Models\Vehicle;
use App\Services\PricingEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PricingEngineTest extends TestCase
{
    use RefreshDatabase;

    protected Vehicle $vehicle;

    protected PricingEngine $engine;

    protected function setUp(): void
    {
        parent::setUp();

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

        SystemSetting::create(['key' => 'tax_rate', 'value' => '11', 'group_name' => 'finance']);
        SystemSetting::create(['key' => 'driver_fee_standard', 'value' => '200000', 'group_name' => 'pricing']);

        $this->engine = new PricingEngine;
    }

    public function test_basic_price_calculation(): void
    {
        $result = $this->engine->calculateRentalPrice(
            $this->vehicle,
            '2026-10-01',
            '2026-10-04',
            'self_drive'
        );

        $this->assertEquals(500000, $result['daily_rate']);
        $this->assertEquals(3, $result['duration_days']);
        $this->assertEquals(1500000, $result['base_total']);
        $this->assertEquals(1500000, $result['subtotal']);
        $this->assertEquals(1665000, $result['total']);
    }

    public function test_promo_code_discount(): void
    {
        PromoVoucher::create([
            'code' => 'HEMAT10',
            'name' => 'Hemat 10%',
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'is_active' => true,
            'start_date' => now()->subMonth(),
            'end_date' => now()->addMonth(),
        ]);

        $result = $this->engine->calculateRentalPrice(
            $this->vehicle,
            '2026-10-01',
            '2026-10-04',
            'self_drive',
            null,
            'HEMAT10'
        );

        $this->assertTrue($result['breakdown']['promo_applied']);
        $this->assertEquals(150000, $result['discount_amount']);
        $this->assertEquals(1350000, $result['after_discount']);
    }

    public function test_late_fee_calculation(): void
    {
        $fee = $this->engine->calculateLateFee($this->vehicle, 90);
        $this->assertEquals(50000, $fee);

        $fee = $this->engine->calculateLateFee($this->vehicle, 0);
        $this->assertEquals(0.0, $fee);

        $fee = $this->engine->calculateLateFee($this->vehicle, 1500);
        $this->assertEquals(400000, $fee);
    }

    public function test_driver_fee_added(): void
    {
        $resultWithDriver = $this->engine->calculateRentalPrice(
            $this->vehicle,
            '2026-10-01',
            '2026-10-04',
            'with_driver'
        );

        $resultSelfDrive = $this->engine->calculateRentalPrice(
            $this->vehicle,
            '2026-10-01',
            '2026-10-04',
            'self_drive'
        );

        $this->assertGreaterThan($resultSelfDrive['total'], $resultWithDriver['total']);

        $driverFee = $this->engine->calculateDriverFee(3);
        $this->assertEquals(600000, $driverFee);
    }

    public function test_tax_calculation(): void
    {
        $result = $this->engine->calculateRentalPrice(
            $this->vehicle,
            '2026-10-01',
            '2026-10-04',
            'self_drive'
        );

        $expectedTax = round(1500000 * 0.11, 2);
        $this->assertEquals($expectedTax, $result['tax_amount']);
        $this->assertEquals(1500000 + $expectedTax, $result['total']);
    }
}

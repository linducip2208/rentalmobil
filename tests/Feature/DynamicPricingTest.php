<?php

namespace Tests\Feature;

use App\Models\SeasonPeriod;
use App\Models\SystemSetting;
use App\Models\Vehicle;
use App\Services\PricingEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DynamicPricingTest extends TestCase
{
    use RefreshDatabase;

    private function vehicle(): Vehicle
    {
        return Vehicle::create(['name' => 'Avanza Dinamis', 'slug' => 'avanza-dinamis-'.uniqid(), 'category_id' => 1, 'brand_id' => 1, 'location_id' => 1, 'plate_number' => 'B '.rand(1000, 9999).' DP', 'year' => 2024, 'color' => 'Putih', 'transmission' => 'automatic', 'seat_count' => 7, 'daily_rate' => 500000, 'weekly_rate' => 3000000, 'monthly_rate' => 10000000, 'deposit_amount' => 500000, 'status' => 'available', 'is_active' => true]);
    }

    public function test_season_multiplier_applied_on_matching_dates(): void
    {
        $vehicle = $this->vehicle();

        SeasonPeriod::create([
            'name' => 'Libur Lebaran', 'start_date' => now()->addDays(30), 'end_date' => now()->addDays(34),
            'multiplier' => 1.5, 'is_active' => true,
        ]);

        $inSeason = app(PricingEngine::class)->calculateRentalPrice($vehicle, now()->addDays(30)->toDateString(), now()->addDays(31)->toDateString());
        $offSeason = app(PricingEngine::class)->calculateRentalPrice($vehicle, now()->addDays(40)->toDateString(), now()->addDays(41)->toDateString());

        $this->assertSame(750000.0, (float) $inSeason['effective_daily_rate']);
        $this->assertSame(500000.0, (float) $offSeason['effective_daily_rate']);
        $this->assertTrue($inSeason['breakdown']['surge_applied']);
    }

    public function test_recurring_annual_season_matches_any_year(): void
    {
        $vehicle = $this->vehicle();

        SeasonPeriod::create([
            'name' => 'Tahun Baru', 'start_date' => '2020-12-29', 'end_date' => '2021-01-03',
            'multiplier' => 1.3, 'is_recurring_annual' => true, 'is_active' => true,
        ]);

        $pricing = app(PricingEngine::class)->calculateRentalPrice($vehicle, '2030-12-30', '2030-12-31');

        $this->assertSame(650000.0, (float) $pricing['effective_daily_rate']);
    }

    public function test_demand_pricing_high_occupancy_raises_rate(): void
    {
        $vehicle = $this->vehicle();
        SystemSetting::set('demand_pricing', json_encode([
            'enabled' => true, 'high_threshold' => 0.5, 'high_multiplier' => 1.2, 'low_threshold' => 0.1, 'low_multiplier' => 0.9,
        ]));

        // Buat 1 order aktif pada 1 kendaraan kategori ini -> occupancy tinggi (1/1 kendaraan).
        $customer = \App\Models\Customer::create(['name' => 'Demand Uji', 'email' => 'dm'.uniqid().'@test.local', 'phone' => '0824', 'customer_type' => 'individual', 'verification_status' => 'verified', 'is_active' => true]);
        \App\Models\RentalOrder::create([
            'customer_id' => $customer->id, 'vehicle_id' => $vehicle->id, 'location_id' => 1,
            'start_date' => now()->subDays(20), 'end_date' => now()->addDays(5),
            'rental_type' => 'self_drive', 'duration_days' => 25, 'daily_rate_snapshot' => 500000,
            'subtotal' => 12500000, 'addon_total' => 0, 'discount_total' => 0, 'tax_total' => 0,
            'final_amount' => 12500000, 'amount_paid' => 0, 'balance_due' => 12500000,
            'deposit_amount' => 0, 'status' => 'active',
        ]);

        $pricing = app(PricingEngine::class)->calculateRentalPrice($vehicle, now()->addDays(2)->toDateString(), now()->addDays(3)->toDateString());

        $this->assertNotNull($pricing['breakdown']['demand_occupancy']);
        $this->assertSame(600000.0, (float) $pricing['effective_daily_rate']);
    }

    public function test_demand_pricing_disabled_returns_base_rate(): void
    {
        $vehicle = $this->vehicle();

        $pricing = app(PricingEngine::class)->calculateRentalPrice($vehicle, now()->addDays(2)->toDateString(), now()->addDays(3)->toDateString());

        $this->assertSame(500000.0, (float) $pricing['effective_daily_rate']);
    }
}

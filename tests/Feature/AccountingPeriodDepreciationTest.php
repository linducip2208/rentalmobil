<?php

namespace Tests\Feature;

use App\Models\AccountingPeriod;
use App\Models\Brand;
use App\Models\Category;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\Location;
use App\Models\Vehicle;
use App\Models\VehicleDepreciationRun;
use App\Services\VehicleDepreciationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

class AccountingPeriodDepreciationTest extends TestCase
{
    use RefreshDatabase;

    public function test_closed_period_blocks_posting_in_domain_layer(): void
    {
        AccountingPeriod::create(['fiscal_year' => 2026, 'period_number' => 1, 'start_date' => '2026-01-01', 'end_date' => '2026-01-31', 'status' => 'closed']);
        $this->expectException(RuntimeException::class);
        JournalEntry::create(['date' => '2026-01-15', 'description' => 'Blocked', 'total_debit' => 100, 'total_credit' => 100, 'status' => 'posted']);
    }

    public function test_vehicle_depreciation_is_balanced_residual_aware_and_idempotent(): void
    {
        $suffix = Str::lower(Str::random(8));
        $location = Location::create(['name' => 'Cabang '.$suffix, 'slug' => 'branch-'.$suffix, 'is_active' => true]);
        $category = Category::create(['name' => 'MPV '.$suffix, 'slug' => 'mpv-'.$suffix, 'is_active' => true]);
        $brand = Brand::create(['name' => 'Brand '.$suffix, 'slug' => 'brand-'.$suffix, 'is_active' => true]);
        $expense = ChartOfAccount::create(['code' => '55'.$suffix, 'name' => 'Beban Depresiasi', 'type' => 'expense', 'normal_balance' => 'debit', 'is_active' => true]);
        $accumulated = ChartOfAccount::create(['code' => '15'.$suffix, 'name' => 'Akumulasi Depresiasi', 'type' => 'asset', 'normal_balance' => 'credit', 'is_active' => true]);
        $vehicle = Vehicle::create(['location_id' => $location->id, 'category_id' => $category->id, 'brand_id' => $brand->id, 'name' => 'Toyota Test', 'plate_number' => 'B '.random_int(1000, 9999).' TST', 'year' => 2025, 'fuel_type' => 'pertamax', 'transmission' => 'automatic', 'daily_rate' => 500000, 'weekly_rate' => 3000000, 'monthly_rate' => 10000000, 'status' => 'available', 'is_active' => true, 'purchase_price' => 120000000, 'residual_value' => 24000000, 'useful_life_months' => 48, 'depreciation_method' => 'straight_line', 'depreciation_expense_account_id' => $expense->id, 'accumulated_depreciation_account_id' => $accumulated->id]);
        $period = AccountingPeriod::create(['fiscal_year' => 2026, 'period_number' => 2, 'start_date' => '2026-02-01', 'end_date' => '2026-02-28', 'status' => 'open']);

        $first = app(VehicleDepreciationService::class)->post($vehicle, $period);
        $second = app(VehicleDepreciationService::class)->post($vehicle->fresh(), $period);

        $this->assertSame($first->id, $second->id);
        $this->assertSame(2000000.0, (float) $first->amount);
        $this->assertSame(1, VehicleDepreciationRun::count());
        $entry = $first->journalEntry()->with('lines')->firstOrFail();
        $this->assertTrue($entry->isBalanced());
        $this->assertSame(2000000.0, (float) $entry->lines->sum('debit'));
        $this->assertSame(2000000.0, (float) $entry->lines->sum('credit'));
    }
}

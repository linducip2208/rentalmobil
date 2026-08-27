<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Location;
use App\Models\RiskRule;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\RiskEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class GrowthAutomationTest extends TestCase
{
    use RefreshDatabase;

    private function vehicle(array $extra = []): Vehicle
    {
        return Vehicle::create(array_merge(['name' => 'Growth Car', 'category_id' => 1, 'brand_id' => 1, 'location_id' => 1, 'plate_number' => 'B 9000 QA', 'year' => 2025, 'transmission' => 'automatic', 'fuel_type' => 'pertamax', 'seat_count' => 5, 'daily_rate' => 500000, 'weekly_rate' => 3000000, 'monthly_rate' => 10000000, 'deposit_amount' => 500000, 'status' => 'available', 'is_active' => true], $extra));
    }

    public function test_quote_uses_database_pricing_engine(): void
    {
        $v = $this->vehicle();
        $this->postJson(route('booking.quote'), ['vehicle_id' => $v->id, 'start_date' => now()->addDay()->toDateString(), 'end_date' => now()->addDays(3)->toDateString(), 'rental_type' => 'self_drive'])->assertOk()->assertJsonPath('duration_days', 2)->assertJsonStructure(['effective_daily_rate', 'tax_amount', 'total']);
    }

    public function test_risk_rules_are_loaded_from_database(): void
    {
        $c = Customer::create(['name' => 'Risk User', 'email' => 'risk@test.local', 'phone' => '0800', 'customer_type' => 'individual', 'trust_score' => 50, 'verification_status' => 'submitted', 'is_active' => true]);
        RiskRule::create(['name' => 'Nilai tinggi', 'field' => 'booking_amount', 'operator' => 'gte', 'comparison_value' => '10000000', 'score_delta' => -40, 'action' => 'block', 'is_active' => true]);
        $a = app(RiskEngine::class)->assess(['booking_amount' => 12000000], $c);
        $this->assertSame('block', $a->decision);
        $this->assertCount(1, $a->matched_rules);
    }

    public function test_booking_success_requires_signed_url(): void
    {
        $customer = Customer::create(['name' => 'Signed User', 'email' => 'signed@test.local', 'phone' => '0811', 'customer_type' => 'individual', 'trust_score' => 50, 'verification_status' => 'submitted', 'is_active' => true]);
        $vehicle = $this->vehicle();
        $booking = Booking::create(['booking_code' => 'BKG-SIGNED', 'customer_id' => $customer->id, 'vehicle_id' => $vehicle->id, 'pickup_location_id' => 1, 'return_location_id' => 1, 'start_date' => now()->addDay(), 'end_date' => now()->addDays(2), 'rental_type' => 'self_drive', 'status' => 'inquiry']);
        $this->get("/booking/{$booking->id}/berhasil")->assertForbidden();
    }

    public function test_location_user_only_sees_own_branch_vehicles(): void
    {
        $ownBranch = Location::findOrFail(1);
        $otherBranch = Location::create(['name' => 'Cabang Dua', 'slug' => 'cabang-dua-'.Str::lower(Str::random(6)), 'is_active' => true]);
        $own = $this->vehicle(['plate_number' => 'B 9001 QA', 'location_id' => $ownBranch->id]);
        $other = $this->vehicle(['plate_number' => 'B 9002 QA', 'location_id' => $otherBranch->id]);
        $user = User::findOrFail(1);
        $user->update(['location_id' => $ownBranch->id]);
        $this->actingAs($user);
        $ids = Vehicle::pluck('id');
        $this->assertTrue($ids->contains($own->id));
        $this->assertFalse($ids->contains($other->id));
    }
}

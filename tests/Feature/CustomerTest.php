<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\TrustScoreLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_customer(): void
    {
        $customer = Customer::create([
            'name' => 'Siti Rahayu',
            'customer_type' => 'individual',
            'email' => 'siti@test.com',
            'phone' => '081298765432',
            'address' => 'Jl. Sudirman No. 10',
            'city' => 'Jakarta',
            'province' => 'DKI Jakarta',
            'postal_code' => '10220',
            'ktp_number' => '3175012345678901',
            'sim_number' => '310123456789',
            'date_of_birth' => '1990-05-15',
            'gender' => 'female',
            'emergency_contact_name' => 'Ahmad Rahayu',
            'emergency_contact_phone' => '081211112222',
            'trust_score' => 85,
            'total_spent' => 0,
            'total_orders' => 0,
            'loyalty_tier' => 'bronze',
            'verification_status' => 'verified',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'name' => 'Siti Rahayu',
            'email' => 'siti@test.com',
            'trust_score' => 85,
        ]);
    }

    public function test_customer_has_relationships(): void
    {
        $customer = Customer::create([
            'name' => 'Ahmad Fauzi',
            'customer_type' => 'individual',
            'email' => 'ahmad@test.com',
            'phone' => '081333334444',
            'trust_score' => 100,
            'total_spent' => 0,
            'total_orders' => 0,
            'verification_status' => 'pending',
            'is_active' => true,
        ]);

        $this->assertEmpty($customer->bookings);
        $this->assertEmpty($customer->documents);
        $this->assertEmpty($customer->payments);

        $this->assertTrue($customer->bookings()->count() === 0);
        $this->assertTrue($customer->documents()->count() === 0);
        $this->assertTrue($customer->payments()->count() === 0);
    }

    public function test_customer_scopes(): void
    {
        Customer::create([
            'name' => 'Active Verified',
            'customer_type' => 'individual',
            'email' => 'active@test.com',
            'phone' => '081111111111',
            'trust_score' => 100,
            'total_spent' => 0,
            'total_orders' => 0,
            'verification_status' => 'verified',
            'is_active' => true,
        ]);

        Customer::create([
            'name' => 'Inactive Corporate',
            'customer_type' => 'corporate',
            'email' => 'corp@test.com',
            'phone' => '081222222222',
            'trust_score' => 50,
            'total_spent' => 0,
            'total_orders' => 0,
            'verification_status' => 'pending',
            'is_active' => false,
        ]);

        $this->assertCount(1, Customer::active()->get());
        $this->assertCount(1, Customer::verified()->get());
        $this->assertCount(1, Customer::individual()->get());
        $this->assertCount(1, Customer::corporate()->get());
    }

    public function test_trust_score_changes(): void
    {
        $customer = Customer::create([
            'name' => 'Rina Wati',
            'customer_type' => 'individual',
            'email' => 'rina@test.com',
            'phone' => '081555556666',
            'trust_score' => 80,
            'total_spent' => 0,
            'total_orders' => 0,
            'verification_status' => 'verified',
            'is_active' => true,
        ]);

        TrustScoreLog::create([
            'customer_id' => $customer->id,
            'previous_score' => 80,
            'new_score' => 90,
            'change_reason' => 'Pengembalian tepat waktu',
            'changed_by' => 1,
        ]);

        TrustScoreLog::create([
            'customer_id' => $customer->id,
            'previous_score' => 90,
            'new_score' => 75,
            'change_reason' => 'Keterlambatan pengembalian',
            'changed_by' => 1,
        ]);

        $this->assertCount(2, $customer->trustScoreLogs);
        $this->assertCount(1, TrustScoreLog::where('customer_id', $customer->id)->increases()->get());
        $this->assertCount(1, TrustScoreLog::where('customer_id', $customer->id)->decreases()->get());
    }
}

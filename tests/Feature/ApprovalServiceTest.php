<?php

namespace Tests\Feature;

use App\Models\ApprovalWorkflow;
use App\Models\AuditLog;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\ApprovalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApprovalServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ApprovalService $service;

    protected function setUp(): void
    {
        parent::setUp();

        SystemSetting::create([
            'key' => 'approval_threshold',
            'value' => '10000000',
            'group_name' => 'approval',
        ]);

        $this->service = new ApprovalService();
    }

    public function test_high_value_needs_approval(): void
    {
        $result = $this->service->checkApprovalRequired('rental_order', 15000000);
        $this->assertTrue($result);
    }

    public function test_low_value_no_approval(): void
    {
        $result = $this->service->checkApprovalRequired('rental_order', 5000000);
        $this->assertFalse($result);
    }

    public function test_zero_amount_no_approval(): void
    {
        $result = $this->service->checkApprovalRequired('rental_order', 0);
        $this->assertFalse($result);
    }

    public function test_can_approve_workflow(): void
    {
        $user = User::create([
            'name' => 'Manager',
            'email' => 'manager@test.com',
            'password' => bcrypt('password'),
        ]);

        $workflow = ApprovalWorkflow::create([
            'type' => 'expense',
            'reference_type' => 'App\\Models\\RentalOrder',
            'reference_id' => 1,
            'requested_by' => $user->id,
            'status' => 'pending',
            'amount' => 15000000,
        ]);

        $result = $this->service->approve($workflow, $user->id, 'Disetujui karena sudah sesuai SOP');

        $this->assertTrue($result);
        $workflow->refresh();
        $this->assertEquals('approved', $workflow->status);
        $this->assertNotNull($workflow->approved_at);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'approval_approved',
            'auditable_type' => ApprovalWorkflow::class,
            'auditable_id' => $workflow->id,
        ]);
    }

    public function test_can_reject_workflow(): void
    {
        $user = User::create([
            'name' => 'Manager',
            'email' => 'manager@test.com',
            'password' => bcrypt('password'),
        ]);

        $workflow = ApprovalWorkflow::create([
            'type' => 'expense',
            'reference_type' => 'App\\Models\\RentalOrder',
            'reference_id' => 1,
            'requested_by' => $user->id,
            'status' => 'pending',
            'amount' => 15000000,
        ]);

        $result = $this->service->reject($workflow, $user->id, 'Dokumen tidak lengkap');

        $this->assertTrue($result);
        $workflow->refresh();
        $this->assertEquals('rejected', $workflow->status);
        $this->assertNotNull($workflow->rejected_at);
        $this->assertEquals('Dokumen tidak lengkap', $workflow->reason);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'approval_rejected',
            'auditable_type' => ApprovalWorkflow::class,
            'auditable_id' => $workflow->id,
        ]);
    }

    public function test_cannot_approve_twice(): void
    {
        $user = User::create([
            'name' => 'Manager',
            'email' => 'manager@test.com',
            'password' => bcrypt('password'),
        ]);

        $workflow = ApprovalWorkflow::create([
            'type' => 'expense',
            'reference_type' => 'App\\Models\\RentalOrder',
            'reference_id' => 1,
            'requested_by' => $user->id,
            'status' => 'pending',
            'amount' => 15000000,
        ]);

        $this->service->approve($workflow, $user->id);

        $result = $this->service->approve($workflow, $user->id);
        $this->assertFalse($result);
    }
}

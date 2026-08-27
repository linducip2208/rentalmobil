<?php

namespace App\Services;

use App\Models\ApprovalWorkflow;
use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class ApprovalService
{
    /**
     * Check if a transaction requires approval based on its amount.
     */
    public function checkApprovalRequired(string $entityType, float $amount): bool
    {
        if ($amount <= 0) {
            return false;
        }

        $threshold = $this->getThreshold();

        return $amount >= $threshold;
    }

    /**
     * Get the approval threshold amount from system settings.
     */
    public function getThreshold(): float
    {
        $value = SystemSetting::get('approval_threshold', '10000000');

        return (float) $value;
    }

    /**
     * Submit an entity for approval.
     */
    public function submitForApproval(
        Model $entity,
        string $entityType,
        int $submittedBy
    ): ApprovalWorkflow {
        $amount = 0.0;
        if (method_exists($entity, 'getAttribute')) {
            $amount = (float) ($entity->getAttribute('total_amount')
                ?? $entity->getAttribute('final_amount')
                ?? $entity->getAttribute('amount')
                ?? 0);
        }

        return ApprovalWorkflow::firstOrCreate([
            'reference_type' => get_class($entity),
            'reference_id' => $entity->getKey(),
            'type' => $entityType,
            'status' => 'pending',
        ], [
            'requested_by' => $submittedBy,
            'amount' => $amount,
        ]);
    }

    /**
     * Approve a workflow entry.
     */
    public function approve(ApprovalWorkflow $workflow, int $approvedBy, ?string $notes = null): bool
    {
        if ($workflow->status !== 'pending') {
            return false;
        }

        $oldValues = [
            'status' => $workflow->status,
            'approved_by' => $workflow->approved_by,
            'approved_at' => $workflow->approved_at,
        ];

        $workflow->update([
            'status' => 'approved',
            'approved_by' => $approvedBy,
            'approved_at' => now(),
            'notes' => $notes,
        ]);

        $this->executeApprovedWorkflow($workflow, $approvedBy);

        $approver = User::find($approvedBy);
        $this->createAuditLog(
            'approval_approved',
            $approver,
            $workflow,
            $oldValues,
            [
                'status' => 'approved',
                'approved_by' => $approvedBy,
                'approved_at' => now()->toDateTimeString(),
            ]
        );

        return true;
    }

    protected function executeApprovedWorkflow(ApprovalWorkflow $workflow, int $approvedBy): void
    {
        $reference = $workflow->reference;

        if ($workflow->type === 'booking' && $reference instanceof Booking && $reference->status !== 'converted') {
            app(BookingService::class)->convertToOrder($reference);
        }

        if ($workflow->type === 'payment' && $reference instanceof Payment && $reference->status === 'pending') {
            app(PaymentService::class)->verifyPayment($reference, $approvedBy);
        }

        if ($workflow->type === 'expense' && $reference instanceof Expense && $reference->status === 'pending') {
            $reference->update(['status' => 'approved', 'approved_by' => $approvedBy, 'approved_at' => now()]);
            app(AccountingService::class)->recordExpense($reference);
        }
    }

    /**
     * Reject a workflow entry.
     */
    public function reject(ApprovalWorkflow $workflow, int $rejectedBy, string $reason): bool
    {
        if ($workflow->status !== 'pending') {
            return false;
        }

        $oldValues = [
            'status' => $workflow->status,
            'approved_by' => $workflow->approved_by,
        ];

        $workflow->update([
            'status' => 'rejected',
            'approved_by' => $rejectedBy,
            'rejected_at' => now(),
            'reason' => $reason,
        ]);

        if ($workflow->reference instanceof Expense) {
            $workflow->reference->update(['status' => 'rejected', 'approved_by' => $rejectedBy, 'rejection_reason' => $reason]);
        }

        $rejector = User::find($rejectedBy);
        $this->createAuditLog(
            'approval_rejected',
            $rejector,
            $workflow,
            $oldValues,
            [
                'status' => 'rejected',
                'approved_by' => $rejectedBy,
                'rejected_at' => now()->toDateTimeString(),
                'reason' => $reason,
            ]
        );

        return true;
    }

    /**
     * Get all pending approvals, optionally filtered by user.
     */
    public function getPendingApprovals(?int $userId = null): Collection
    {
        $query = ApprovalWorkflow::query()
            ->where('status', 'pending')
            ->with(['requestedBy', 'approvedBy', 'reference']);

        if ($userId !== null) {
            $query->where('requested_by', $userId);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Escalate a workflow to higher authority.
     */
    public function escalate(ApprovalWorkflow $workflow, string $reason): void
    {
        $oldValues = ['status' => $workflow->status];

        $workflow->update([
            'status' => 'escalated',
            'notes' => $reason,
        ]);

        $this->createAuditLog(
            'approval_escalated',
            auth()->user(),
            $workflow,
            $oldValues,
            [
                'status' => 'escalated',
                'reason' => $reason,
            ]
        );
    }

    /**
     * Auto-escalate approvals pending for more than 24 hours.
     */
    public function autoEscalateStale(): int
    {
        $stale = ApprovalWorkflow::query()
            ->where('status', 'pending')
            ->where('created_at', '<', now()->subHours(24))
            ->get();

        $count = 0;
        foreach ($stale as $workflow) {
            $this->escalate($workflow, 'Otomatis: melebihi batas waktu 24 jam tanpa keputusan.');
            $count++;
        }

        return $count;
    }

    /**
     * Create an audit log entry.
     */
    public function createAuditLog(
        string $action,
        ?Model $user = null,
        ?Model $auditable = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): AuditLog {
        return AuditLog::create([
            'user_id' => $user?->getKey(),
            'action' => $action,
            'auditable_type' => $auditable ? get_class($auditable) : null,
            'auditable_id' => $auditable?->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
        ]);
    }
}

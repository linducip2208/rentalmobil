<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\PurchaseRequisition;
use App\Models\SparePartPurchaseOrder;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ProcurementService
{
    public function submit(PurchaseRequisition $requisition): PurchaseRequisition
    {
        return $this->transition($requisition, ['draft'], 'pending_approval', ['submitted_at' => now()]);
    }

    public function approve(PurchaseRequisition $requisition, ?string $notes = null): PurchaseRequisition
    {
        return $this->transition($requisition, ['submitted', 'pending_approval'], 'approved', ['approved_by' => auth()->id(), 'approved_at' => now(), 'decision_notes' => $notes]);
    }

    public function reject(PurchaseRequisition $requisition, string $reason): PurchaseRequisition
    {
        return $this->transition($requisition, ['submitted', 'pending_approval'], 'rejected', ['approved_by' => auth()->id(), 'rejected_at' => now(), 'decision_notes' => $reason]);
    }

    public function cancel(PurchaseRequisition $requisition, string $reason): PurchaseRequisition
    {
        return $this->transition($requisition, ['draft', 'submitted', 'pending_approval', 'approved'], 'cancelled', ['decision_notes' => $reason]);
    }

    public function convertToPurchaseOrder(PurchaseRequisition $requisition, int $supplierId): SparePartPurchaseOrder
    {
        return DB::transaction(function () use ($requisition, $supplierId) {
            $requisition = PurchaseRequisition::query()->with('items.sparePart')->lockForUpdate()->findOrFail($requisition->id);
            if ($requisition->status === 'converted_to_po' && $existing = $requisition->purchaseOrders()->first()) {
                return $existing;
            }
            if ($requisition->status !== 'approved') {
                throw new RuntimeException('Hanya requisition approved yang dapat dikonversi.');
            }
            if ($requisition->items->isEmpty()) {
                throw new RuntimeException('Purchase requisition tidak memiliki item.');
            }
            $po = SparePartPurchaseOrder::create(['supplier_id' => $supplierId, 'location_id' => $requisition->location_id, 'warehouse_id' => $requisition->warehouse_id, 'purchase_requisition_id' => $requisition->id, 'supplier_name' => Supplier::findOrFail($supplierId)->name, 'status' => 'draft', 'order_date' => now(), 'expected_at' => $requisition->required_date, 'subtotal' => $requisition->estimated_total, 'total_amount' => $requisition->estimated_total, 'created_by' => auth()->id(), 'notes' => $requisition->notes]);
            foreach ($requisition->items as $item) {
                $po->items()->create(['spare_part_id' => $item->spare_part_id, 'quantity' => $item->quantity, 'unit_price' => $item->estimated_unit_price, 'line_total' => $item->estimated_total]);
            }
            $requisition->update(['status' => 'converted_to_po']);
            $this->audit('procurement.requisition_converted', $requisition, ['purchase_order_id' => $po->id]);

            return $po->load('items');
        }, 3);
    }

    private function transition(PurchaseRequisition $requisition, array $from, string $to, array $extra = []): PurchaseRequisition
    {
        return DB::transaction(function () use ($requisition, $from, $to, $extra) {
            $locked = PurchaseRequisition::query()->lockForUpdate()->findOrFail($requisition->id);
            if (! in_array($locked->status, $from, true)) {
                throw new RuntimeException("Transisi requisition {$locked->status} ke {$to} tidak valid.");
            } $old = $locked->status;
            $locked->update(array_merge(['status' => $to], $extra));
            $this->audit("procurement.requisition_{$to}", $locked, ['old_status' => $old, 'new_status' => $to]);

            return $locked;
        }, 3);
    }

    private function audit(string $action, PurchaseRequisition $record, array $values): void
    {
        AuditLog::create(['user_id' => auth()->id(), 'action' => $action, 'auditable_type' => $record->getMorphClass(), 'auditable_id' => $record->id, 'new_values' => $values, 'branch_id' => $record->location_id]);
    }
}

<?php

namespace App\Services;

use App\Models\GoodsReceipt;
use App\Models\SparePartPurchaseOrder;
use App\Models\SparePartPurchaseOrderItem;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class GoodsReceiptService
{
    public function __construct(private readonly InventoryService $inventory) {}

    public function confirm(GoodsReceipt $receipt): GoodsReceipt
    {
        return DB::transaction(function () use ($receipt) {
            $receipt = GoodsReceipt::query()->with(['items.sparePart', 'warehouse'])->lockForUpdate()->findOrFail($receipt->id);
            if ($receipt->status === 'confirmed') {
                return $receipt;
            }
            if ($receipt->status !== 'draft') {
                throw new RuntimeException('Hanya penerimaan draft yang dapat dikonfirmasi.');
            }

            $po = SparePartPurchaseOrder::query()->with('items')->lockForUpdate()->findOrFail($receipt->spare_part_purchase_order_id);
            if (! in_array($po->status, ['approved', 'sent', 'partially_received'], true)) {
                throw new RuntimeException('Purchase order belum disetujui atau sudah tidak dapat diterima.');
            }

            foreach ($receipt->items as $received) {
                $poItem = SparePartPurchaseOrderItem::query()->lockForUpdate()->findOrFail($received->spare_part_purchase_order_item_id);
                if ($poItem->spare_part_purchase_order_id !== $po->id || $poItem->spare_part_id !== $received->spare_part_id) {
                    throw new RuntimeException('Item penerimaan tidak sesuai purchase order.');
                }
                $remaining = (float) $poItem->quantity - (float) $poItem->received_quantity;
                if ((float) $received->accepted_quantity <= 0 || (float) $received->accepted_quantity > $remaining + 0.0005) {
                    throw new RuntimeException('Kuantitas penerimaan melebihi sisa purchase order.');
                }

                $this->inventory->move($receipt->warehouse, $received->sparePart, 'purchase_receipt', (float) $received->accepted_quantity, (float) $received->unit_cost, $receipt);
                $poItem->increment('received_quantity', (float) $received->accepted_quantity);
            }

            $hasRemaining = $po->items()->whereColumn('received_quantity', '<', 'quantity')->exists();
            $po->update(['status' => $hasRemaining ? 'partially_received' : 'received', 'received_at' => $hasRemaining ? null : now()]);
            $receipt->update(['status' => 'confirmed', 'received_at' => now(), 'received_by' => $receipt->received_by ?? auth()->id()]);

            return $receipt->fresh(['items', 'purchaseOrder']);
        }, 3);
    }
}

<?php

namespace App\Services;

use App\Models\SparePart;
use App\Models\SparePartPurchaseOrder;
use App\Models\SparePartPurchaseOrderItem;
use Illuminate\Support\Facades\DB;

/**
 * Draft PO otomatis untuk suku cadang di bawah stok minimum,
 * dikelompokkan per supplier. Jumlah order = min_stock * reorder_multiple - stock.
 */
class PurchaseOrderService
{
    public function draftForLowStock(?int $reorderMultiple = null): array
    {
        $multiple = max(1, $reorderMultiple ?? (int) \App\Models\SystemSetting::get('sparepart_reorder_multiple', 2));

        $lowStockParts = SparePart::query()
            ->whereColumn('stock', '<=', 'min_stock')
            ->where('min_stock', '>', 0)
            ->get();

        if ($lowStockParts->isEmpty()) {
            return ['created' => 0, 'parts' => 0];
        }

        $bySupplier = $lowStockParts->groupBy(fn ($p) => trim($p->supplier_name) ?: 'Supplier Umum');
        $created = 0;

        foreach ($bySupplier as $supplier => $parts) {
            DB::transaction(function () use ($supplier, $parts, $multiple, &$created) {
                $po = SparePartPurchaseOrder::create([
                    'supplier_name' => $supplier,
                    'supplier_phone' => $parts->first()->supplier_phone,
                    'status' => 'draft',
                    'expected_at' => now()->addDays(7)->toDateString(),
                ]);

                $total = 0.0;

                foreach ($parts as $part) {
                    // Sudah ada di PO draft lain? Skip agar tidak double-order.
                    $alreadyDrafted = SparePartPurchaseOrderItem::where('spare_part_id', $part->id)
                        ->whereHas('purchaseOrder', fn ($q) => $q->whereIn('status', ['draft', 'sent']))
                        ->exists();

                    if ($alreadyDrafted) {
                        continue;
                    }

                    $qty = max(1, ($part->min_stock * $multiple) - $part->stock);
                    $lineTotal = round($qty * (float) $part->unit_price, 2);
                    $total += $lineTotal;

                    SparePartPurchaseOrderItem::create([
                        'spare_part_purchase_order_id' => $po->id,
                        'spare_part_id' => $part->id,
                        'quantity' => $qty,
                        'unit_price' => $part->unit_price,
                        'line_total' => $lineTotal,
                    ]);
                }

                if ($po->items()->count() === 0) {
                    $po->delete();
                    return;
                }

                $po->update(['total_amount' => round($total, 2)]);
                $created++;
            });
        }

        return ['created' => $created, 'parts' => $lowStockParts->count()];
    }
}

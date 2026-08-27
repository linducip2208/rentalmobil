<?php

namespace App\Services;

use App\Models\InventoryStock;
use App\Models\SparePart;
use App\Models\StockMovement;
use App\Models\SystemSetting;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class InventoryService
{
    public function move(Warehouse $warehouse, SparePart $part, string $type, float $quantity, float $unitCost = 0, ?Model $reference = null, ?string $note = null): StockMovement
    {
        if (abs($quantity) < 0.0005) {
            throw new RuntimeException('Kuantitas pergerakan stok tidak boleh nol.');
        }

        return DB::transaction(function () use ($warehouse, $part, $type, $quantity, $unitCost, $reference, $note) {
            $stock = InventoryStock::query()->firstOrCreate(
                ['warehouse_id' => $warehouse->id, 'spare_part_id' => $part->id],
                ['minimum_stock' => $part->min_stock, 'reorder_level' => $part->min_stock]
            );
            $stock = InventoryStock::query()->lockForUpdate()->findOrFail($stock->id);
            $newOnHand = round((float) $stock->on_hand + $quantity, 3);
            if ($newOnHand < 0 && ! SystemSetting::get('allow_negative_stock', false)) {
                throw new RuntimeException('Stok tidak mencukupi dan stok negatif dinonaktifkan.');
            }

            $newAverage = (float) $stock->average_cost;
            if ($quantity > 0 && $unitCost >= 0) {
                $oldValue = (float) $stock->on_hand * (float) $stock->average_cost;
                $newAverage = $newOnHand > 0 ? round(($oldValue + ($quantity * $unitCost)) / $newOnHand, 2) : 0;
            }
            $stock->update(['on_hand' => $newOnHand, 'average_cost' => $newAverage]);

            $movement = StockMovement::create([
                'movement_number' => 'SM-'.now()->format('ymdHis').'-'.Str::upper(Str::random(6)),
                'warehouse_id' => $warehouse->id, 'spare_part_id' => $part->id, 'type' => $type,
                'quantity' => $quantity, 'unit_cost' => $unitCost, 'total_cost' => round(abs($quantity) * $unitCost, 2),
                'reference_type' => $reference?->getMorphClass(), 'reference_id' => $reference?->getKey(),
                'performed_by' => auth()->id(), 'occurred_at' => now(), 'note' => $note,
            ]);

            // Compatibility aggregate for legacy screens. The warehouse ledger remains authoritative.
            $part->update(['stock' => (int) round(InventoryStock::where('spare_part_id', $part->id)->sum('on_hand'))]);

            return $movement;
        }, 3);
    }
}

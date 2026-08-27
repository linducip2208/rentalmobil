<?php

namespace App\Services;

use App\Models\StockTransfer;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class StockTransferService
{
    public function __construct(private readonly InventoryService $inventory) {}

    public function submit(StockTransfer $transfer): StockTransfer
    {
        return $this->transition($transfer, ['draft'], 'submitted');
    }

    public function approve(StockTransfer $transfer): StockTransfer
    {
        return $this->transition($transfer, ['submitted'], 'approved', ['approved_by' => auth()->id()]);
    }

    public function ship(StockTransfer $transfer): StockTransfer
    {
        return DB::transaction(function () use ($transfer) {
            $transfer = StockTransfer::with(['items.sparePart', 'sourceWarehouse'])->lockForUpdate()->findOrFail($transfer->id);
            if ($transfer->status !== 'approved') {
                throw new RuntimeException('Transfer belum approved.');
            }foreach ($transfer->items as $item) {
                $this->inventory->move($transfer->sourceWarehouse, $item->sparePart, 'transfer_out', -(float) $item->quantity, (float) ($item->sparePart->inventoryStocks()->where('warehouse_id', $transfer->source_warehouse_id)->value('average_cost') ?? 0), $transfer);
            }$transfer->update(['status' => 'in_transit', 'shipped_at' => now()]);

            return $transfer;
        }, 3);
    }

    public function receive(StockTransfer $transfer): StockTransfer
    {
        return DB::transaction(function () use ($transfer) {
            $transfer = StockTransfer::with(['items.sparePart', 'destinationWarehouse'])->lockForUpdate()->findOrFail($transfer->id);
            if ($transfer->status === 'received') {
                return $transfer;
            }if ($transfer->status !== 'in_transit') {
                throw new RuntimeException('Transfer belum dalam perjalanan.');
            }foreach ($transfer->items as $item) {
                $cost = (float) ($item->sparePart->inventoryStocks()->where('warehouse_id', $transfer->source_warehouse_id)->value('average_cost') ?? 0);
                $this->inventory->move($transfer->destinationWarehouse, $item->sparePart, 'transfer_in', (float) $item->quantity, $cost, $transfer);
                $item->update(['received_quantity' => $item->quantity]);
            }$transfer->update(['status' => 'received', 'received_by' => auth()->id(), 'received_at' => now()]);

            return $transfer;
        }, 3);
    }

    private function transition(StockTransfer $transfer, array $from, string $to, array $extra = []): StockTransfer
    {
        return DB::transaction(function () use ($transfer, $from, $to, $extra) {
            $transfer = StockTransfer::lockForUpdate()->findOrFail($transfer->id);
            if (! in_array($transfer->status, $from, true)) {
                throw new RuntimeException('Transisi stock transfer tidak valid.');
            }$transfer->update(array_merge(['status' => $to], $extra));

            return $transfer;
        }, 3);
    }
}

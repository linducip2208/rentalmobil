<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\GoodsReceipt;
use App\Models\InventoryStock;
use App\Models\Location;
use App\Models\SparePart;
use App\Models\SparePartPurchaseOrder;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Services\GoodsReceiptService;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

class ProcurementInventoryFlowTest extends TestCase
{
    use RefreshDatabase;

    private Warehouse $warehouse;

    private SparePart $part;

    protected function setUp(): void
    {
        parent::setUp();
        $suffix = Str::lower(Str::random(8));
        $location = Location::create(['name' => 'Cabang Test '.$suffix, 'slug' => 'cabang-test-'.$suffix, 'is_active' => true]);
        $this->warehouse = Warehouse::create(['location_id' => $location->id, 'code' => 'WH-'.Str::upper($suffix), 'name' => 'Gudang Test '.$suffix, 'is_active' => true]);
        $category = Category::create(['name' => 'Suku Cadang '.$suffix, 'slug' => 'suku-cadang-'.$suffix, 'is_active' => true]);
        $this->part = SparePart::create(['name' => 'Filter Oli', 'part_number' => 'FO-'.$suffix, 'category_id' => $category->id, 'unit_price' => 50000, 'stock' => 0, 'min_stock' => 2]);
    }

    public function test_stock_increases_only_when_goods_receipt_is_confirmed_and_is_idempotent(): void
    {
        $po = SparePartPurchaseOrder::create(['po_number' => 'PO-TST-1', 'warehouse_id' => $this->warehouse->id, 'location_id' => $this->warehouse->location_id, 'supplier_name' => 'PT Supplier Test', 'status' => 'approved', 'total_amount' => 500000]);
        $poItem = $po->items()->create(['spare_part_id' => $this->part->id, 'quantity' => 10, 'unit_price' => 50000, 'line_total' => 500000]);
        $receipt = GoodsReceipt::create(['receipt_number' => 'GR-TST-1', 'spare_part_purchase_order_id' => $po->id, 'warehouse_id' => $this->warehouse->id, 'status' => 'draft']);
        $receipt->items()->create(['spare_part_purchase_order_item_id' => $poItem->id, 'spare_part_id' => $this->part->id, 'accepted_quantity' => 4, 'unit_cost' => 50000]);

        $this->assertDatabaseMissing('inventory_stocks', ['spare_part_id' => $this->part->id]);
        app(GoodsReceiptService::class)->confirm($receipt);
        app(GoodsReceiptService::class)->confirm($receipt->fresh());

        $stock = InventoryStock::firstOrFail();
        $this->assertSame(4.0, (float) $stock->on_hand);
        $this->assertSame(50000.0, (float) $stock->average_cost);
        $this->assertSame(1, StockMovement::count());
        $this->assertSame('partially_received', $po->fresh()->status);
    }

    public function test_over_receipt_is_rejected_without_stock_mutation(): void
    {
        $po = SparePartPurchaseOrder::create(['po_number' => 'PO-TST-2', 'warehouse_id' => $this->warehouse->id, 'supplier_name' => 'Supplier', 'status' => 'approved']);
        $item = $po->items()->create(['spare_part_id' => $this->part->id, 'quantity' => 2, 'unit_price' => 50000, 'line_total' => 100000]);
        $receipt = GoodsReceipt::create(['receipt_number' => 'GR-TST-2', 'spare_part_purchase_order_id' => $po->id, 'warehouse_id' => $this->warehouse->id]);
        $receipt->items()->create(['spare_part_purchase_order_item_id' => $item->id, 'spare_part_id' => $this->part->id, 'accepted_quantity' => 3, 'unit_cost' => 50000]);

        try {
            app(GoodsReceiptService::class)->confirm($receipt);
            $this->fail('Over receipt seharusnya ditolak.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('melebihi', $e->getMessage());
        }
        $this->assertSame(0, StockMovement::count());
    }

    public function test_negative_stock_is_blocked(): void
    {
        $this->expectException(RuntimeException::class);
        app(InventoryService::class)->move($this->warehouse, $this->part, 'maintenance_issue', -1, 50000);
    }

    public function test_weighted_average_cost_is_calculated(): void
    {
        $service = app(InventoryService::class);
        $service->move($this->warehouse, $this->part, 'opening_balance', 2, 10000);
        $service->move($this->warehouse, $this->part, 'purchase_receipt', 2, 20000);
        $this->assertSame(15000.0, (float) InventoryStock::firstOrFail()->average_cost);
    }
}

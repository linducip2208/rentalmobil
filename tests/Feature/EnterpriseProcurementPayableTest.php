<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\ChartOfAccount;
use App\Models\InventoryStock;
use App\Models\JournalEntry;
use App\Models\Location;
use App\Models\PurchaseRequisition;
use App\Models\SparePart;
use App\Models\StockTransfer;
use App\Models\Supplier;
use App\Models\SupplierInvoice;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\FinancialStatementService;
use App\Services\InventoryService;
use App\Services\ProcurementService;
use App\Services\StockTransferService;
use App\Services\SupplierPayableService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

class EnterpriseProcurementPayableTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Location $branch;

    private Warehouse $warehouse;

    private SparePart $part;

    private Supplier $supplier;

    protected function setUp(): void
    {
        parent::setUp();
        $suffix = Str::lower(Str::random(8));
        $this->user = User::findOrFail(1);
        $this->actingAs($this->user);
        $this->branch = Location::create(['name' => 'Cabang Enterprise '.$suffix, 'slug' => 'enterprise-'.$suffix, 'is_active' => true]);
        $this->warehouse = Warehouse::create(['location_id' => $this->branch->id, 'code' => 'WH-'.Str::upper($suffix), 'name' => 'Gudang '.$suffix, 'is_active' => true]);
        $category = Category::create(['name' => 'Parts '.$suffix, 'slug' => 'parts-'.$suffix, 'is_active' => true]);
        $this->part = SparePart::create(['name' => 'Brake Pad', 'part_number' => 'BP-'.$suffix, 'category_id' => $category->id, 'unit_price' => 100000, 'stock' => 0, 'min_stock' => 2]);
        $this->supplier = Supplier::create(['code' => 'SUP-'.Str::upper($suffix), 'name' => 'PT Supplier '.$suffix, 'payment_terms_days' => 30, 'is_active' => true]);
    }

    public function test_requisition_transitions_and_idempotent_conversion_to_po(): void
    {
        $pr = PurchaseRequisition::create(['location_id' => $this->branch->id, 'warehouse_id' => $this->warehouse->id, 'requested_by' => $this->user->id, 'request_date' => now(), 'required_date' => now()->addDays(5), 'priority' => 'high', 'status' => 'draft', 'estimated_total' => 500000]);
        $pr->items()->create(['spare_part_id' => $this->part->id, 'quantity' => 5, 'estimated_unit_price' => 100000, 'estimated_total' => 500000]);
        $service = app(ProcurementService::class);
        $service->submit($pr);
        $service->approve($pr->fresh(), 'Budget tersedia');
        $first = $service->convertToPurchaseOrder($pr->fresh(), $this->supplier->id);
        $second = $service->convertToPurchaseOrder($pr->fresh(), $this->supplier->id);
        $this->assertSame($first->id, $second->id);
        $this->assertSame('converted_to_po', $pr->fresh()->status);
        $this->assertCount(1, $first->items);
        $this->assertSame(1, $pr->purchaseOrders()->count());
    }

    public function test_supplier_bill_posts_balanced_journal_and_supports_partial_full_payment(): void
    {
        $this->ensureAccount('1300', 'Persediaan', 'asset', 'debit');
        $this->ensureAccount('1301', 'Pajak Masukan', 'asset', 'debit');
        $this->ensureAccount('2100', 'Hutang Usaha', 'liability', 'credit');
        $this->ensureAccount('1101', 'Kas Bank', 'asset', 'debit');
        $bill = SupplierInvoice::create(['supplier_id' => $this->supplier->id, 'location_id' => $this->branch->id, 'invoice_date' => now(), 'due_date' => now()->addDays(30), 'subtotal' => 1000000, 'tax_amount' => 110000, 'discount_amount' => 0, 'total' => 1110000, 'status' => 'draft', 'created_by' => $this->user->id]);
        $service = app(SupplierPayableService::class);
        $entry = $service->post($bill);
        $again = $service->post($bill->fresh());
        $this->assertSame($entry->id, $again->id);
        $this->assertEqualsWithDelta($entry->lines()->sum('debit'), $entry->lines()->sum('credit'), .01);
        $service->pay($bill->fresh(), 400000);
        $this->assertSame('partial', $bill->fresh()->status);
        $this->assertSame(710000.0, $bill->fresh()->outstanding_amount);
        $service->pay($bill->fresh(), 710000);
        $this->assertSame('paid', $bill->fresh()->status);
        $this->assertSame(0.0, $bill->fresh()->outstanding_amount);
        $trial = app(FinancialStatementService::class)->trialBalance(now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString(), $this->branch->id);
        $this->assertTrue($trial['is_balanced']);
        $this->assertSame(1110000.0, $trial['total_debit']);
    }

    public function test_stock_transfer_posts_out_and_in_once(): void
    {
        $destination = Warehouse::create(['location_id' => $this->branch->id, 'code' => 'WH-DST-'.Str::upper(Str::random(4)), 'name' => 'Gudang Tujuan '.Str::random(4), 'is_active' => true]);
        app(InventoryService::class)->move($this->warehouse, $this->part, 'opening_balance', 10, 100000);
        $transfer = StockTransfer::create(['source_warehouse_id' => $this->warehouse->id, 'destination_warehouse_id' => $destination->id, 'transfer_date' => now(), 'status' => 'draft', 'requested_by' => $this->user->id]);
        $transfer->items()->create(['spare_part_id' => $this->part->id, 'quantity' => 4]);
        $service = app(StockTransferService::class);
        $service->submit($transfer);
        $service->approve($transfer->fresh());
        $service->ship($transfer->fresh());
        $service->receive($transfer->fresh());
        $service->receive($transfer->fresh());
        $this->assertSame(6.0, (float) InventoryStock::where('warehouse_id', $this->warehouse->id)->value('on_hand'));
        $this->assertSame(4.0, (float) InventoryStock::where('warehouse_id', $destination->id)->value('on_hand'));
        $this->assertSame(1, $transfer->stockMovements()->where('type', 'transfer_out')->count());
        $this->assertSame(1, $transfer->stockMovements()->where('type', 'transfer_in')->count());
    }

    public function test_invalid_requisition_transition_is_rejected(): void
    {
        $pr = PurchaseRequisition::create(['location_id' => $this->branch->id, 'warehouse_id' => $this->warehouse->id, 'requested_by' => $this->user->id, 'request_date' => now(), 'status' => 'draft']);
        $this->expectException(RuntimeException::class);
        app(ProcurementService::class)->approve($pr);
    }

    public function test_unbalanced_posted_journal_is_rejected(): void
    {
        $this->expectException(RuntimeException::class);
        JournalEntry::create(['date' => now(), 'description' => 'Unbalanced', 'total_debit' => 100, 'total_credit' => 90, 'status' => 'posted']);
    }

    private function ensureAccount(string $code, string $name, string $type, string $normal): void
    {
        ChartOfAccount::firstOrCreate(['code' => $code], ['name' => $name, 'type' => $type, 'normal_balance' => $normal, 'is_active' => true]);
    }
}

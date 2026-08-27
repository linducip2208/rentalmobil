<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->string('tax_number', 40)->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 40)->nullable();
            $table->text('address')->nullable();
            $table->unsignedSmallInteger('payment_terms_days')->default(30);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_id')->constrained('locations')->restrictOnDelete();
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->text('address')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->unique(['location_id', 'name']);
        });

        Schema::table('spare_part_purchase_orders', function (Blueprint $table) {
            $table->foreignId('supplier_id')->nullable()->after('po_number')->constrained('suppliers')->restrictOnDelete();
            $table->foreignId('location_id')->nullable()->after('supplier_id')->constrained('locations')->restrictOnDelete();
            $table->foreignId('warehouse_id')->nullable()->after('location_id')->constrained('warehouses')->restrictOnDelete();
            $table->date('order_date')->nullable()->after('status');
            $table->decimal('subtotal', 16, 2)->default(0)->after('total_amount');
            $table->decimal('tax_amount', 16, 2)->default(0)->after('subtotal');
            $table->decimal('discount_amount', 16, 2)->default(0)->after('tax_amount');
            $table->foreignId('approved_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->index(['location_id', 'status']);
            $table->index(['supplier_id', 'status']);
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE spare_part_purchase_orders MODIFY status VARCHAR(30) NOT NULL DEFAULT 'draft'");
        }

        Schema::create('inventory_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained('warehouses')->restrictOnDelete();
            $table->foreignId('spare_part_id')->constrained('spare_parts')->restrictOnDelete();
            $table->decimal('on_hand', 16, 3)->default(0);
            $table->decimal('reserved', 16, 3)->default(0);
            $table->decimal('average_cost', 16, 2)->default(0);
            $table->decimal('minimum_stock', 16, 3)->default(0);
            $table->decimal('reorder_level', 16, 3)->default(0);
            $table->decimal('reorder_quantity', 16, 3)->default(0);
            $table->timestamps();
            $table->unique(['warehouse_id', 'spare_part_id']);
        });

        Schema::create('goods_receipts', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_number', 40)->unique();
            $table->foreignId('spare_part_purchase_order_id')->constrained('spare_part_purchase_orders')->restrictOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->restrictOnDelete();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('supplier_delivery_note')->nullable();
            $table->string('status', 30)->default('draft')->index();
            $table->timestamp('received_at')->nullable()->index();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['spare_part_purchase_order_id', 'status'], 'gr_po_status_idx');
        });

        Schema::create('goods_receipt_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goods_receipt_id')->constrained('goods_receipts')->cascadeOnDelete();
            $table->foreignId('spare_part_purchase_order_item_id')->constrained('spare_part_purchase_order_items')->restrictOnDelete();
            $table->foreignId('spare_part_id')->constrained('spare_parts')->restrictOnDelete();
            $table->decimal('accepted_quantity', 16, 3)->default(0);
            $table->decimal('rejected_quantity', 16, 3)->default(0);
            $table->decimal('damaged_quantity', 16, 3)->default(0);
            $table->decimal('unit_cost', 16, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['goods_receipt_id', 'spare_part_purchase_order_item_id'], 'gr_item_unique');
        });

        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->string('movement_number', 50)->unique();
            $table->foreignId('warehouse_id')->constrained('warehouses')->restrictOnDelete();
            $table->foreignId('spare_part_id')->constrained('spare_parts')->restrictOnDelete();
            $table->string('type', 40)->index();
            $table->decimal('quantity', 16, 3);
            $table->decimal('unit_cost', 16, 2)->default(0);
            $table->decimal('total_cost', 16, 2)->default(0);
            $table->nullableMorphs('reference');
            $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('occurred_at')->index();
            $table->text('note')->nullable();
            $table->timestamps();
            $table->index(['warehouse_id', 'spare_part_id', 'occurred_at'], 'stock_ledger_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('goods_receipt_items');
        Schema::dropIfExists('goods_receipts');
        Schema::dropIfExists('inventory_stocks');
        Schema::table('spare_part_purchase_orders', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
            $table->dropForeign(['location_id']);
            $table->dropForeign(['warehouse_id']);
            $table->dropForeign(['approved_by']);
            $table->dropColumn(['supplier_id', 'location_id', 'warehouse_id', 'order_date', 'subtotal', 'tax_amount', 'discount_amount', 'approved_by', 'approved_at']);
        });
        Schema::dropIfExists('warehouses');
        Schema::dropIfExists('suppliers');
    }
};

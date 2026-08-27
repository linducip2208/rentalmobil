<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spare_part_purchase_orders', function (Blueprint $t) {
            $t->id();
            $t->string('po_number', 30)->unique();
            $t->string('supplier_name');
            $t->string('supplier_phone', 30)->nullable();
            $t->enum('status', ['draft', 'sent', 'received', 'cancelled'])->default('draft');
            $t->date('expected_at')->nullable();
            $t->decimal('total_amount', 16, 2)->default(0);
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('received_at')->nullable();
            $t->text('notes')->nullable();
            $t->timestamps();
        });

        Schema::create('spare_part_purchase_order_items', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('spare_part_purchase_order_id');
            $t->foreign('spare_part_purchase_order_id', 'sp_po_items_order_fk')
                ->references('id')
                ->on('spare_part_purchase_orders')
                ->cascadeOnDelete();
            $t->foreignId('spare_part_id')->constrained()->restrictOnDelete();
            $t->unsignedInteger('quantity')->default(1);
            $t->unsignedInteger('received_quantity')->default(0);
            $t->decimal('unit_price', 14, 2)->default(0);
            $t->decimal('line_total', 16, 2)->default(0);
            $t->timestamps();
        });

        Schema::table('vehicles', function (Blueprint $t) {
            $t->decimal('purchase_price', 16, 2)->nullable();
            $t->unsignedSmallInteger('useful_life_months')->nullable();
            $t->date('acquired_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', fn (Blueprint $t) => $t->dropColumn(['purchase_price', 'useful_life_months', 'acquired_at']));
        Schema::dropIfExists('spare_part_purchase_order_items');
        Schema::dropIfExists('spare_part_purchase_orders');
    }
};

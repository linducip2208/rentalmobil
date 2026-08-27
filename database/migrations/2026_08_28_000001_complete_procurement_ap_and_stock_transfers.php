<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->foreignId('location_id')->nullable()->after('posting_key')->constrained('locations')->restrictOnDelete();
            $table->index(['location_id', 'date', 'status'], 'journal_branch_date_status_idx');
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('contact_person')->nullable()->after('name');
            $table->decimal('credit_limit', 16, 2)->default(0)->after('payment_terms_days');
            $table->json('bank_details')->nullable()->after('credit_limit');
            $table->decimal('rating', 3, 2)->nullable()->after('bank_details');
            $table->text('notes')->nullable()->after('rating');
        });

        Schema::create('supplier_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->string('name');
            $table->string('position')->nullable();
            $table->string('phone', 40)->nullable();
            $table->string('email')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
            $table->index(['supplier_id', 'is_primary']);
        });

        Schema::create('purchase_requisitions', function (Blueprint $table) {
            $table->id();
            $table->string('requisition_number', 40)->unique();
            $table->foreignId('location_id')->constrained('locations')->restrictOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->restrictOnDelete();
            $table->foreignId('requested_by')->constrained('users')->restrictOnDelete();
            $table->string('department', 100)->nullable();
            $table->date('request_date')->index();
            $table->date('required_date')->nullable()->index();
            $table->string('priority', 20)->default('normal')->index();
            $table->string('status', 30)->default('draft')->index();
            $table->decimal('estimated_total', 16, 2)->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->text('decision_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['location_id', 'status', 'request_date'], 'pr_branch_status_date_idx');
        });

        Schema::create('purchase_requisition_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_requisition_id')->constrained('purchase_requisitions')->cascadeOnDelete();
            $table->foreignId('spare_part_id')->constrained('spare_parts')->restrictOnDelete();
            $table->decimal('quantity', 16, 3);
            $table->decimal('estimated_unit_price', 16, 2)->default(0);
            $table->decimal('estimated_total', 16, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['purchase_requisition_id', 'spare_part_id'], 'pr_part_unique');
        });

        Schema::table('spare_part_purchase_orders', function (Blueprint $table) {
            $table->foreignId('purchase_requisition_id')->nullable()->after('warehouse_id')->constrained('purchase_requisitions')->restrictOnDelete();
            $table->index('purchase_requisition_id');
        });

        Schema::create('supplier_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('bill_number', 40)->unique();
            $table->string('supplier_invoice_number')->nullable();
            $table->foreignId('supplier_id')->constrained('suppliers')->restrictOnDelete();
            $table->foreignId('location_id')->constrained('locations')->restrictOnDelete();
            $table->foreignId('spare_part_purchase_order_id')->nullable()->constrained('spare_part_purchase_orders')->restrictOnDelete();
            $table->foreignId('goods_receipt_id')->nullable()->constrained('goods_receipts')->restrictOnDelete();
            $table->date('invoice_date')->index();
            $table->date('due_date')->index();
            $table->decimal('subtotal', 16, 2)->default(0);
            $table->decimal('tax_amount', 16, 2)->default(0);
            $table->decimal('discount_amount', 16, 2)->default(0);
            $table->decimal('total', 16, 2)->default(0);
            $table->decimal('paid_amount', 16, 2)->default(0);
            $table->string('status', 20)->default('draft')->index();
            $table->json('attachments')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('posted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['supplier_id', 'supplier_invoice_number'], 'supplier_external_bill_unique');
            $table->index(['location_id', 'status', 'due_date'], 'ap_branch_status_due_idx');
        });

        Schema::create('supplier_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_invoice_id')->constrained('supplier_invoices')->cascadeOnDelete();
            $table->foreignId('spare_part_id')->nullable()->constrained('spare_parts')->restrictOnDelete();
            $table->string('description');
            $table->decimal('quantity', 16, 3)->default(1);
            $table->decimal('unit_price', 16, 2)->default(0);
            $table->decimal('line_total', 16, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('supplier_payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_number', 40)->unique();
            $table->foreignId('supplier_invoice_id')->constrained('supplier_invoices')->restrictOnDelete();
            $table->foreignId('supplier_id')->constrained('suppliers')->restrictOnDelete();
            $table->foreignId('location_id')->constrained('locations')->restrictOnDelete();
            $table->foreignId('bank_account_id')->nullable()->constrained('bank_accounts')->restrictOnDelete();
            $table->date('payment_date')->index();
            $table->decimal('amount', 16, 2);
            $table->string('reference')->nullable();
            $table->string('status', 20)->default('posted')->index();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['supplier_id', 'payment_date']);
        });

        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_number', 40)->unique();
            $table->foreignId('source_warehouse_id')->constrained('warehouses')->restrictOnDelete();
            $table->foreignId('destination_warehouse_id')->constrained('warehouses')->restrictOnDelete();
            $table->string('status', 20)->default('draft')->index();
            $table->date('transfer_date')->index();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['source_warehouse_id', 'status']);
            $table->index(['destination_warehouse_id', 'status']);
        });

        Schema::create('stock_transfer_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_transfer_id')->constrained('stock_transfers')->cascadeOnDelete();
            $table->foreignId('spare_part_id')->constrained('spare_parts')->restrictOnDelete();
            $table->decimal('quantity', 16, 3);
            $table->decimal('received_quantity', 16, 3)->default(0);
            $table->timestamps();
            $table->unique(['stock_transfer_id', 'spare_part_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_transfer_items');
        Schema::dropIfExists('stock_transfers');
        Schema::dropIfExists('supplier_payments');
        Schema::dropIfExists('supplier_invoice_items');
        Schema::dropIfExists('supplier_invoices');
        Schema::table('spare_part_purchase_orders', function (Blueprint $table) {
            $table->dropForeign(['purchase_requisition_id']);
            $table->dropColumn('purchase_requisition_id');
        });
        Schema::dropIfExists('purchase_requisition_items');
        Schema::dropIfExists('purchase_requisitions');
        Schema::dropIfExists('supplier_contacts');
        Schema::table('suppliers', fn (Blueprint $table) => $table->dropColumn(['contact_person', 'credit_limit', 'bank_details', 'rating', 'notes']));
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->dropForeign(['location_id']);
            $table->dropColumn('location_id');
        });
    }
};

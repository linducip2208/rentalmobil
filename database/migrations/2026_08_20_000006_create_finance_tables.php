<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $t) {
            $t->id();
            $t->string('invoice_number')->unique()->index();
            $t->foreignId('rental_order_id')->nullable()->constrained('rental_orders')->restrictOnDelete();
            $t->foreignId('customer_id')->constrained('customers')->restrictOnDelete();
            $t->enum('type', ['rental', 'additional', 'penalty', 'refund'])->default('rental');
            $t->decimal('subtotal', 14, 2)->default(0);
            $t->decimal('tax_amount', 14, 2)->default(0);
            $t->decimal('discount_amount', 14, 2)->default(0);
            $t->decimal('total_amount', 14, 2)->default(0);
            $t->decimal('amount_paid', 14, 2)->default(0);
            $t->decimal('balance_due', 14, 2)->default(0);
            $t->date('due_date')->nullable();
            $t->enum('status', [
                'draft', 'issued', 'partially_paid', 'paid', 'overdue', 'cancelled', 'voided',
            ])->default('draft');
            $t->timestamp('paid_at')->nullable();
            $t->text('notes')->nullable();
            $t->string('pdf_path')->nullable();
            $t->timestamps();
            $t->softDeletes();

            $t->index('customer_id');
            $t->index('status');
            $t->index('due_date');
        });

        Schema::create('payments', function (Blueprint $t) {
            $t->id();
            $t->string('payment_number')->unique()->index();
            $t->foreignId('invoice_id')->nullable()->constrained('invoices')->restrictOnDelete();
            $t->foreignId('rental_order_id')->nullable()->constrained('rental_orders')->restrictOnDelete();
            $t->foreignId('customer_id')->constrained('customers')->restrictOnDelete();
            $t->foreignId('payment_method_id')->nullable()->constrained('payment_methods')->restrictOnDelete();
            $t->decimal('amount', 14, 2);
            $t->date('payment_date');
            $t->time('payment_time')->nullable();
            $t->string('reference_number')->nullable();
            $t->string('proof_url')->nullable();
            $t->enum('status', ['pending', 'verified', 'rejected', 'voided', 'refunded'])->default('pending');
            $t->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('verified_at')->nullable();
            $t->timestamp('voided_at')->nullable();
            $t->text('void_reason')->nullable();
            $t->text('notes')->nullable();
            $t->timestamps();

            $t->index('customer_id');
            $t->index('status');
            $t->index('payment_date');
        });

        Schema::create('deposits', function (Blueprint $t) {
            $t->id();
            $t->foreignId('customer_id')->constrained('customers')->restrictOnDelete();
            $t->foreignId('rental_order_id')->nullable()->constrained('rental_orders')->restrictOnDelete();
            $t->foreignId('booking_id')->nullable()->constrained('bookings')->restrictOnDelete();
            $t->decimal('amount', 14, 2);
            $t->enum('deposit_status', [
                'expected', 'received', 'held', 'partially_deducted',
                'refunded', 'disputed', 'forfeited', 'cancelled',
            ])->default('expected');
            $t->foreignId('payment_method_id')->nullable()->constrained('payment_methods')->restrictOnDelete();
            $t->string('reference_number')->nullable();
            $t->string('proof_url')->nullable();
            $t->text('notes')->nullable();
            $t->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('received_at')->nullable();
            $t->timestamp('refunded_at')->nullable();
            $t->decimal('refund_amount', 12, 2)->default(0);
            $t->string('refund_method')->nullable();
            $t->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
        });

        Schema::create('expense_categories', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('code')->nullable();
            $t->text('description')->nullable();
            $t->foreignId('parent_id')->nullable()->constrained('expense_categories')->restrictOnDelete();
            $t->boolean('is_active')->default(true);
            $t->unsignedSmallInteger('sort_order')->default(0);
            $t->timestamps();
        });

        Schema::create('expenses', function (Blueprint $t) {
            $t->id();
            $t->string('expense_number')->unique()->index();
            $t->foreignId('expense_category_id')->nullable()->constrained('expense_categories')->restrictOnDelete();
            $t->foreignId('location_id')->nullable()->constrained('locations')->restrictOnDelete();
            $t->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $t->string('title');
            $t->text('description')->nullable();
            $t->decimal('amount', 14, 2);
            $t->date('expense_date');
            $t->foreignId('payment_method_id')->nullable()->constrained('payment_methods')->restrictOnDelete();
            $t->string('receipt_url')->nullable();
            $t->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $t->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('approved_at')->nullable();
            $t->text('rejection_reason')->nullable();
            $t->string('reference_type')->nullable();
            $t->unsignedBigInteger('reference_id')->nullable();
            $t->timestamps();
        });

        Schema::create('bank_accounts', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('bank_name')->nullable();
            $t->string('account_number')->nullable();
            $t->string('account_name')->nullable();
            $t->enum('account_type', ['saving', 'current', 'cash'])->default('saving');
            $t->decimal('balance', 16, 2)->default(0);
            $t->decimal('initial_balance', 16, 2)->default(0);
            $t->boolean('is_active')->default(true);
            $t->text('notes')->nullable();
            $t->timestamps();
        });

        Schema::create('chart_of_accounts', function (Blueprint $t) {
            $t->id();
            $t->string('code')->unique();
            $t->string('name');
            $t->text('description')->nullable();
            $t->enum('type', ['asset', 'liability', 'equity', 'revenue', 'expense']);
            $t->foreignId('parent_id')->nullable()->constrained('chart_of_accounts')->restrictOnDelete();
            $t->boolean('is_active')->default(true);
            $t->boolean('is_system')->default(false);
            $t->enum('normal_balance', ['debit', 'credit'])->nullable();
            $t->timestamps();
        });

        Schema::create('journal_entries', function (Blueprint $t) {
            $t->id();
            $t->string('entry_number')->unique()->index();
            $t->date('date');
            $t->string('description');
            $t->string('reference_type')->nullable();
            $t->unsignedBigInteger('reference_id')->nullable();
            $t->decimal('total_debit', 16, 2)->default(0);
            $t->decimal('total_credit', 16, 2)->default(0);
            $t->enum('status', ['draft', 'posted', 'reversed'])->default('draft');
            $t->foreignId('posted_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('posted_at')->nullable();
            $t->timestamp('reversed_at')->nullable();
            $t->foreignId('reversal_entry_id')->nullable()->constrained('journal_entries')->restrictOnDelete();
            $t->timestamps();
        });

        Schema::create('journal_lines', function (Blueprint $t) {
            $t->id();
            $t->foreignId('journal_entry_id')->constrained('journal_entries')->cascadeOnDelete();
            $t->foreignId('account_id')->constrained('chart_of_accounts')->restrictOnDelete();
            $t->text('description')->nullable();
            $t->decimal('debit', 16, 2)->default(0);
            $t->decimal('credit', 16, 2)->default(0);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_lines');
        Schema::dropIfExists('journal_entries');
        Schema::dropIfExists('chart_of_accounts');
        Schema::dropIfExists('bank_accounts');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('expense_categories');
        Schema::dropIfExists('deposits');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoices');
    }
};

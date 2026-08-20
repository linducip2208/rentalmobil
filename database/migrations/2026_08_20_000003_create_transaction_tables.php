<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained();
            $table->string('booking_number')->unique();
            $table->foreignId('vehicle_id')->constrained();
            $table->foreignId('pickup_location_id')->constrained('locations');
            $table->foreignId('return_location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->enum('rental_type', ['self_drive', 'with_driver'])->default('self_drive');
            $table->foreignId('driver_id')->nullable()->constrained()->nullOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->date('estimated_return_date');
            $table->time('pickup_time')->nullable();
            $table->time('return_time')->nullable();
            $table->decimal('subtotal', 12, 2);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2);
            $table->decimal('deposit_amount', 12, 2)->default(0);
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'converted_to_order'])->default('pending');
            $table->text('notes')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('rental_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->constrained();
            $table->foreignId('vehicle_id')->constrained();
            $table->foreignId('driver_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('location_id')->constrained('locations');
            $table->string('order_number')->unique();
            $table->enum('rental_type', ['self_drive', 'with_driver'])->default('self_drive');
            $table->enum('status', [
                'pending',
                'confirmed',
                'dispatched',
                'active',
                'return_pending',
                'returned',
                'completed',
                'cancelled',
            ])->default('pending');
            $table->date('start_date');
            $table->date('end_date');
            $table->date('actual_return_date')->nullable();
            $table->integer('pickup_km')->nullable();
            $table->integer('return_km')->nullable();
            $table->integer('pickup_fuel_level')->nullable();
            $table->integer('return_fuel_level')->nullable();
            $table->decimal('subtotal', 12, 2);
            $table->decimal('addon_total', 12, 2)->default(0);
            $table->decimal('discount_total', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2);
            $table->decimal('total_amount', 12, 2);
            $table->decimal('deposit_amount', 12, 2)->default(0);
            $table->decimal('amount_paid', 12, 2)->default(0);
            $table->decimal('amount_refunded', 12, 2)->default(0);
            $table->decimal('balance_due', 12, 2)->default(0);
            $table->decimal('late_fee', 12, 2)->default(0);
            $table->decimal('damage_fee', 12, 2)->default(0);
            $table->decimal('final_amount', 12, 2)->nullable();
            $table->text('notes')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('dispatched_at')->nullable();
            $table->timestamp('checked_out_at')->nullable();
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('rental_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('addon_id')->constrained();
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('total_price', 12, 2);
            $table->string('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_order_id')->constrained();
            $table->foreignId('customer_id')->constrained();
            $table->string('invoice_number')->unique();
            $table->enum('type', ['rental', 'additional', 'penalty', 'refund']);
            $table->decimal('subtotal', 12, 2);
            $table->decimal('tax_amount', 12, 2);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2);
            $table->decimal('amount_paid', 12, 2)->default(0);
            $table->decimal('balance_due', 12, 2);
            $table->enum('status', ['draft', 'sent', 'paid', 'partially_paid', 'overdue', 'cancelled'])->default('draft');
            $table->date('due_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('rental_order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->constrained();
            $table->string('payment_number')->unique();
            $table->foreignId('payment_method_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 12, 2);
            $table->date('payment_date');
            $table->time('payment_time')->nullable();
            $table->string('reference_number')->nullable();
            $table->string('proof_url')->nullable();
            $table->enum('status', ['pending', 'verified', 'rejected', 'cancelled'])->default('pending');
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('return_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_order_id')->constrained()->cascadeOnDelete();
            $table->date('return_date');
            $table->time('return_time');
            $table->date('actual_return_date')->nullable();
            $table->integer('return_km')->nullable();
            $table->integer('return_fuel_level')->nullable();
            $table->text('condition_notes')->nullable();
            $table->boolean('has_damage')->default(false);
            $table->decimal('damage_total', 12, 2)->default(0);
            $table->decimal('fuel_charge', 12, 2)->default(0);
            $table->decimal('late_charge', 12, 2)->default(0);
            $table->decimal('other_charges', 12, 2)->default(0);
            $table->decimal('total_charges', 12, 2)->default(0);
            $table->decimal('deposit_refund', 12, 2)->default(0);
            $table->foreignId('inspector_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('photos')->nullable();
            $table->enum('status', ['pending_review', 'approved', 'disputed'])->default('pending_review');
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('damage_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('return_record_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('rental_order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('vehicle_id')->constrained();
            $table->foreignId('reported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('damage_type', [
                'scratch',
                'dent',
                'broken_glass',
                'tire',
                'interior',
                'mechanical',
                'electrical',
                'other',
            ]);
            $table->enum('severity', ['minor', 'moderate', 'major', 'critical'])->default('minor');
            $table->text('description');
            $table->string('location_on_vehicle')->nullable();
            $table->decimal('estimated_cost', 12, 2)->default(0);
            $table->decimal('actual_cost', 12, 2)->default(0);
            $table->json('photos')->nullable();
            $table->enum('status', ['reported', 'assessed', 'charged', 'repaired', 'closed'])->default('reported');
            $table->foreignId('assessed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('assessed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('promo_vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('discount_type', ['percentage', 'fixed']);
            $table->decimal('discount_value', 12, 2);
            $table->integer('min_rental_days')->default(1);
            $table->decimal('max_discount', 12, 2)->nullable();
            $table->integer('usage_limit')->nullable();
            $table->integer('used_count')->default(0);
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('voucher_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voucher_id')->constrained('promo_vouchers');
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('rental_order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->constrained();
            $table->decimal('discount_amount', 12, 2);
            $table->timestamp('used_at')->useCurrent();
            $table->timestamps();
        });

        Schema::create('surge_pricing_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('vehicle_category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('location_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('multiplier', 4, 2)->default(1.00);
            $table->date('start_date');
            $table->date('end_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->json('days_of_week')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surge_pricing_rules');
        Schema::dropIfExists('voucher_usages');
        Schema::dropIfExists('promo_vouchers');
        Schema::dropIfExists('damage_reports');
        Schema::dropIfExists('return_records');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('rental_order_items');
        Schema::dropIfExists('rental_orders');
        Schema::dropIfExists('bookings');
    }
};

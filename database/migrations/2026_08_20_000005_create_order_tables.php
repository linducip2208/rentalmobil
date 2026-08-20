<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rental_orders', function (Blueprint $t) {
            $t->id();
            $t->string('order_number')->unique()->index();
            $t->foreignId('booking_id')->nullable()->constrained('bookings')->restrictOnDelete();
            $t->foreignId('customer_id')->constrained('customers')->restrictOnDelete();
            $t->foreignId('vehicle_id')->constrained('vehicles')->restrictOnDelete();
            $t->foreignId('driver_id')->nullable()->constrained('drivers')->restrictOnDelete();
            $t->foreignId('location_id')->constrained('locations')->restrictOnDelete();
            $t->date('start_date');
            $t->date('end_date');
            $t->date('actual_return_date')->nullable();
            $t->enum('rental_type', ['self_drive', 'with_driver', 'airport_transfer', 'corporate'])->default('self_drive');
            $t->unsignedSmallInteger('duration_days')->nullable();
            $t->decimal('daily_rate_snapshot', 12, 2)->nullable();
            $t->decimal('subtotal', 14, 2)->default(0);
            $t->decimal('addon_total', 14, 2)->default(0);
            $t->decimal('discount_total', 14, 2)->default(0);
            $t->decimal('tax_total', 14, 2)->default(0);
            $t->decimal('late_fee', 14, 2)->default(0);
            $t->decimal('damage_fee', 14, 2)->default(0);
            $t->decimal('fuel_charge', 14, 2)->default(0);
            $t->decimal('km_charge', 14, 2)->default(0);
            $t->decimal('final_amount', 14, 2)->default(0);
            $t->decimal('amount_paid', 14, 2)->default(0);
            $t->decimal('balance_due', 14, 2)->default(0);
            $t->decimal('deposit_amount', 14, 2)->default(0);
            $t->decimal('amount_refunded', 14, 2)->default(0);
            $t->enum('status', [
                'draft', 'ready_for_preparation', 'preparing', 'ready_for_handover',
                'checked_out', 'active', 'extension_requested', 'return_due', 'overdue',
                'return_inspection', 'payment_pending', 'completed', 'cancelled', 'disputed',
            ])->default('draft');
            $t->enum('payment_status', ['unpaid', 'partially_paid', 'paid', 'overdue'])->default('unpaid');
            $t->unsignedInteger('pickup_km')->nullable();
            $t->unsignedInteger('return_km')->nullable();
            $t->unsignedTinyInteger('pickup_fuel_level')->nullable();
            $t->unsignedTinyInteger('return_fuel_level')->nullable();
            $t->text('notes')->nullable();
            $t->text('internal_notes')->nullable();
            $t->text('cancellation_reason')->nullable();
            $t->timestamp('cancelled_at')->nullable();
            $t->timestamp('dispatched_at')->nullable();
            $t->timestamp('checked_out_at')->nullable();
            $t->timestamp('checked_in_at')->nullable();
            $t->timestamp('completed_at')->nullable();
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
            $t->softDeletes();

            $t->index('customer_id');
            $t->index('vehicle_id');
            $t->index('driver_id');
            $t->index('status');
            $t->index('start_date');
            $t->index('end_date');
            $t->index('booking_id');
            $t->index('location_id');
            $t->index('payment_status');
        });

        Schema::create('rental_order_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('rental_order_id')->constrained('rental_orders')->cascadeOnDelete();
            $t->foreignId('addon_id')->nullable()->constrained('addons')->restrictOnDelete();
            $t->string('name');
            $t->text('description')->nullable();
            $t->unsignedSmallInteger('quantity')->default(1);
            $t->decimal('unit_price', 12, 2);
            $t->decimal('total_price', 12, 2);
            $t->string('type')->nullable();
            $t->text('notes')->nullable();
            $t->timestamps();
        });

        Schema::create('contracts', function (Blueprint $t) {
            $t->id();
            $t->string('contract_number')->unique()->index();
            $t->foreignId('rental_order_id')->nullable()->constrained('rental_orders')->restrictOnDelete();
            $t->foreignId('booking_id')->nullable()->constrained('bookings')->restrictOnDelete();
            $t->foreignId('customer_id')->constrained('customers')->restrictOnDelete();
            $t->foreignId('vehicle_id')->constrained('vehicles')->restrictOnDelete();
            $t->date('start_date');
            $t->date('end_date');
            $t->enum('rental_type', ['self_drive', 'with_driver', 'airport_transfer', 'corporate']);
            $t->decimal('daily_rate', 12, 2);
            $t->decimal('total_amount', 14, 2);
            $t->decimal('deposit_amount', 14, 2);
            $t->unsignedInteger('km_limit')->nullable();
            $t->string('fuel_policy')->nullable();
            $t->string('usage_area')->nullable();
            $t->text('late_policy')->nullable();
            $t->text('damage_policy')->nullable();
            $t->text('accident_policy')->nullable();
            $t->text('loss_policy')->nullable();
            $t->text('insurance_policy')->nullable();
            $t->string('customer_signature_url')->nullable();
            $t->string('staff_signature_url')->nullable();
            $t->timestamp('signed_at')->nullable();
            $t->string('document_hash')->nullable();
            $t->enum('status', ['draft', 'active', 'signed', 'completed', 'cancelled'])->default('draft');
            $t->unsignedSmallInteger('version')->default(1);
            $t->timestamps();
            $t->softDeletes();
        });

        Schema::create('return_records', function (Blueprint $t) {
            $t->id();
            $t->foreignId('rental_order_id')->constrained('rental_orders')->restrictOnDelete();
            $t->date('actual_return_date');
            $t->time('actual_return_time')->nullable();
            $t->foreignId('return_location_id')->nullable()->constrained('locations')->restrictOnDelete();
            $t->unsignedInteger('return_km')->nullable();
            $t->unsignedTinyInteger('return_fuel_level')->nullable();
            $t->unsignedInteger('extra_km')->default(0);
            $t->unsignedInteger('late_minutes')->default(0);
            $t->string('body_condition')->nullable();
            $t->string('interior_condition')->nullable();
            $t->string('tire_condition')->nullable();
            $t->boolean('has_damage')->default(false);
            $t->text('damage_description')->nullable();
            $t->json('photos')->nullable();
            $t->decimal('late_charge', 12, 2)->default(0);
            $t->decimal('fuel_charge', 12, 2)->default(0);
            $t->decimal('damage_total', 12, 2)->default(0);
            $t->decimal('other_charges', 12, 2)->default(0);
            $t->decimal('total_charges', 12, 2)->default(0);
            $t->decimal('deposit_refund', 12, 2)->default(0);
            $t->foreignId('inspector_id')->nullable()->constrained('users')->nullOnDelete();
            $t->enum('status', ['pending_review', 'approved', 'disputed'])->default('pending_review');
            $t->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('approved_at')->nullable();
            $t->text('rejection_reason')->nullable();
            $t->text('notes')->nullable();
            $t->timestamps();

            $t->index('rental_order_id');
            $t->index('status');
        });

        Schema::create('handover_records', function (Blueprint $t) {
            $t->id();
            $t->foreignId('rental_order_id')->constrained('rental_orders')->restrictOnDelete();
            $t->foreignId('vehicle_id')->constrained('vehicles')->restrictOnDelete();
            $t->foreignId('customer_id')->nullable()->constrained('customers')->restrictOnDelete();
            $t->foreignId('staff_id')->constrained('users')->restrictOnDelete();
            $t->enum('type', ['outbound', 'inbound']);
            $t->json('odometer_readings')->nullable();
            $t->unsignedTinyInteger('fuel_level')->nullable();
            $t->string('body_condition')->nullable();
            $t->string('interior_condition')->nullable();
            $t->json('accessories')->nullable();
            $t->json('checklist')->nullable();
            $t->json('photos')->nullable();
            $t->string('customer_signature_url')->nullable();
            $t->string('staff_signature_url')->nullable();
            $t->text('notes')->nullable();
            $t->timestamp('recorded_at');
            $t->timestamps();
        });

        Schema::create('damage_reports', function (Blueprint $t) {
            $t->id();
            $t->foreignId('return_record_id')->nullable()->constrained('return_records')->restrictOnDelete();
            $t->foreignId('rental_order_id')->nullable()->constrained('rental_orders')->restrictOnDelete();
            $t->foreignId('vehicle_id')->constrained('vehicles')->restrictOnDelete();
            $t->foreignId('customer_id')->nullable()->constrained('customers')->restrictOnDelete();
            $t->foreignId('reported_by')->nullable()->constrained('users')->nullOnDelete();
            $t->enum('damage_type', [
                'scratch', 'dent', 'broken_glass', 'tire', 'interior',
                'mechanical', 'electrical', 'other',
            ]);
            $t->string('location_on_vehicle')->nullable();
            $t->enum('severity', ['minor', 'moderate', 'major', 'critical']);
            $t->text('description')->nullable();
            $t->decimal('estimated_cost', 12, 2)->default(0);
            $t->decimal('actual_cost', 12, 2)->default(0);
            $t->boolean('charged_to_customer')->default(false);
            $t->decimal('charge_amount', 12, 2)->default(0);
            $t->json('photos')->nullable();
            $t->enum('status', ['reported', 'assessed', 'charged', 'repaired', 'closed'])->default('reported');
            $t->foreignId('assessed_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('assessed_at')->nullable();
            $t->text('notes')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('damage_reports');
        Schema::dropIfExists('handover_records');
        Schema::dropIfExists('return_records');
        Schema::dropIfExists('contracts');
        Schema::dropIfExists('rental_order_items');
        Schema::dropIfExists('rental_orders');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('vehicle_id')->constrained('vehicles')->restrictOnDelete();
            $t->enum('type', ['routine', 'repair', 'emergency', 'recall'])->default('routine');
            $t->string('title')->nullable();
            $t->text('description')->nullable();
            $t->string('mechanic_name')->nullable();
            $t->date('start_date')->nullable();
            $t->date('end_date')->nullable();
            $t->unsignedInteger('mileage_at_service')->nullable();
            $t->unsignedInteger('next_service_km')->nullable();
            $t->date('next_service_date')->nullable();
            $t->decimal('cost', 12, 2)->default(0);
            $t->json('parts_used')->nullable();
            $t->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled'])->default('scheduled');
            $t->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();
            $t->string('receipt_url')->nullable();
            $t->text('notes')->nullable();
            $t->timestamps();

            $t->index('vehicle_id');
            $t->index('status');
            $t->index('start_date');
        });

        Schema::create('service_schedules', function (Blueprint $t) {
            $t->id();
            $t->foreignId('vehicle_id')->constrained('vehicles')->restrictOnDelete();
            $t->enum('service_type', [
                'oil_change', 'tire_rotation', 'brake_check',
                'general_inspection', 'battery', 'replacement',
            ]);
            $t->unsignedInteger('interval_km')->nullable();
            $t->unsignedInteger('interval_days')->nullable();
            $t->unsignedInteger('last_service_km')->nullable();
            $t->date('last_service_date')->nullable();
            $t->unsignedInteger('next_service_km')->nullable();
            $t->date('next_service_date')->nullable();
            $t->decimal('estimated_cost', 12, 2)->default(0);
            $t->boolean('is_active')->default(true);
            $t->text('notes')->nullable();
            $t->timestamps();
        });

        Schema::create('fuel_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('vehicle_id')->constrained('vehicles')->restrictOnDelete();
            $t->foreignId('logged_by')->nullable()->constrained('users')->nullOnDelete();
            $t->date('fuel_date');
            $t->enum('fuel_type', ['pertalite', 'pertamax', 'premium', 'diesel']);
            $t->decimal('liters', 8, 2);
            $t->decimal('cost', 12, 2)->default(0);
            $t->unsignedInteger('odometer_km')->nullable();
            $t->string('location')->nullable();
            $t->string('receipt_url')->nullable();
            $t->timestamps();
        });

        Schema::create('km_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('vehicle_id')->constrained('vehicles')->restrictOnDelete();
            $t->foreignId('logged_by')->nullable()->constrained('users')->nullOnDelete();
            $t->date('log_date');
            $t->unsignedInteger('start_km');
            $t->unsignedInteger('end_km');
            $t->string('purpose')->nullable();
            $t->text('notes')->nullable();
            $t->timestamps();
        });

        Schema::create('spare_parts', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('part_number')->nullable();
            $t->foreignId('category_id')->nullable()->constrained('categories')->restrictOnDelete();
            $t->decimal('unit_price', 12, 2)->default(0);
            $t->unsignedInteger('stock')->default(0);
            $t->unsignedInteger('min_stock')->default(5);
            $t->string('location_in_store')->nullable();
            $t->string('supplier_name')->nullable();
            $t->string('supplier_phone')->nullable();
            $t->timestamps();
        });

        Schema::create('gps_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('vehicle_id')->constrained('vehicles')->restrictOnDelete();
            $t->decimal('latitude', 10, 7);
            $t->decimal('longitude', 10, 7);
            $t->decimal('speed', 6, 2)->nullable();
            $t->unsignedSmallInteger('heading')->nullable();
            $t->decimal('accuracy', 6, 2)->nullable();
            $t->unsignedTinyInteger('battery_level')->nullable();
            $t->timestamp('recorded_at');
            $t->timestamps();
        });

        Schema::create('deliveries', function (Blueprint $t) {
            $t->id();
            $t->foreignId('rental_order_id')->nullable()->constrained('rental_orders')->restrictOnDelete();
            $t->foreignId('driver_id')->nullable()->constrained('drivers')->restrictOnDelete();
            $t->foreignId('vehicle_id')->constrained('vehicles')->restrictOnDelete();
            $t->foreignId('from_location_id')->nullable()->constrained('locations')->restrictOnDelete();
            $t->foreignId('to_location_id')->nullable()->constrained('locations')->restrictOnDelete();
            $t->enum('type', ['delivery', 'pickup'])->default('delivery');
            $t->enum('status', ['pending', 'dispatched', 'in_transit', 'completed', 'cancelled'])->default('pending');
            $t->date('scheduled_date')->nullable();
            $t->time('scheduled_time')->nullable();
            $t->date('actual_date')->nullable();
            $t->time('actual_time')->nullable();
            $t->string('from_address')->nullable();
            $t->string('to_address')->nullable();
            $t->text('notes')->nullable();
            $t->json('photos')->nullable();
            $t->string('signature_url')->nullable();
            $t->timestamps();
        });

        Schema::create('transfers', function (Blueprint $t) {
            $t->id();
            $t->string('transfer_number')->unique()->index();
            $t->foreignId('from_location_id')->constrained('locations')->restrictOnDelete();
            $t->foreignId('to_location_id')->constrained('locations')->restrictOnDelete();
            $t->foreignId('vehicle_id')->constrained('vehicles')->restrictOnDelete();
            $t->date('scheduled_date');
            $t->date('completed_date')->nullable();
            $t->enum('status', ['pending', 'in_transit', 'completed', 'cancelled'])->default('pending');
            $t->text('reason')->nullable();
            $t->text('notes')->nullable();
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
        });

        Schema::create('insurance_policies', function (Blueprint $t) {
            $t->id();
            $t->foreignId('vehicle_id')->constrained('vehicles')->restrictOnDelete();
            $t->string('policy_number')->unique();
            $t->string('provider_name');
            $t->enum('coverage_type', ['comprehensive', 'partial', 'total_loss_only']);
            $t->decimal('max_claim', 14, 2)->default(0);
            $t->decimal('premium', 14, 2)->default(0);
            $t->date('start_date');
            $t->date('end_date');
            $t->enum('status', ['active', 'expired', 'cancelled'])->default('active');
            $t->string('document_path')->nullable();
            $t->text('notes')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('insurance_policies');
        Schema::dropIfExists('transfers');
        Schema::dropIfExists('deliveries');
        Schema::dropIfExists('gps_logs');
        Schema::dropIfExists('spare_parts');
        Schema::dropIfExists('km_logs');
        Schema::dropIfExists('fuel_logs');
        Schema::dropIfExists('service_schedules');
        Schema::dropIfExists('maintenance_logs');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained();
            $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('mechanic_name')->nullable();
            $table->enum('type', ['routine', 'repair', 'emergency', 'recall']);
            $table->text('description');
            $table->decimal('cost', 12, 2)->default(0);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->integer('mileage_at_service')->nullable();
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled'])->default('scheduled');
            $table->json('parts_used')->nullable();
            $table->integer('next_service_km')->nullable();
            $table->date('next_service_date')->nullable();
            $table->timestamps();
        });

        Schema::create('service_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->enum('service_type', [
                'oil_change',
                'tire_rotation',
                'brake_check',
                'general_inspection',
                'battery',
                'replacement',
            ]);
            $table->integer('interval_km')->nullable();
            $table->integer('interval_days')->nullable();
            $table->integer('last_service_km')->nullable();
            $table->date('last_service_date')->nullable();
            $table->integer('next_service_km')->nullable();
            $table->date('next_service_date')->nullable();
            $table->decimal('estimated_cost', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('fuel_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained();
            $table->foreignId('logged_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('fuel_date');
            $table->enum('fuel_type', ['pertalite', 'pertamax', 'premium', 'diesel']);
            $table->decimal('liters', 8, 2);
            $table->decimal('cost', 12, 2);
            $table->integer('odometer_km');
            $table->string('location')->nullable();
            $table->string('receipt_url')->nullable();
            $table->timestamps();
        });

        Schema::create('km_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained();
            $table->foreignId('logged_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('log_date');
            $table->integer('start_km');
            $table->integer('end_km');
            $table->string('purpose')->nullable();
            $table->timestamps();
        });

        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('driver_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('vehicle_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('type', ['delivery', 'pickup']);
            $table->date('scheduled_date');
            $table->time('scheduled_time')->nullable();
            $table->date('actual_date')->nullable();
            $table->time('actual_time')->nullable();
            $table->text('from_address')->nullable();
            $table->text('to_address')->nullable();
            $table->foreignId('from_location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->foreignId('to_location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->enum('status', ['pending', 'dispatched', 'in_transit', 'completed', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->json('photos')->nullable();
            $table->string('signature_url')->nullable();
            $table->timestamps();
        });

        Schema::create('transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_location_id')->constrained('locations');
            $table->foreignId('to_location_id')->constrained('locations');
            $table->foreignId('vehicle_id')->constrained();
            $table->string('transfer_number')->unique();
            $table->enum('status', ['pending', 'in_transit', 'completed', 'cancelled'])->default('pending');
            $table->date('scheduled_date')->nullable();
            $table->date('completed_date')->nullable();
            $table->string('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transfers');
        Schema::dropIfExists('deliveries');
        Schema::dropIfExists('km_logs');
        Schema::dropIfExists('fuel_logs');
        Schema::dropIfExists('service_schedules');
        Schema::dropIfExists('maintenance_logs');
    }
};

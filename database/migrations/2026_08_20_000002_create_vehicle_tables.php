<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $t) {
            $t->id();
            $t->foreignId('category_id')->constrained()->restrictOnDelete();
            $t->foreignId('brand_id')->constrained()->restrictOnDelete();
            $t->foreignId('location_id')->constrained()->restrictOnDelete();
            $t->string('name');
            $t->string('slug')->unique();
            $t->text('description')->nullable();
            $t->string('plate_number')->unique();
            $t->unsignedSmallInteger('year');
            $t->string('color')->nullable();
            $t->unsignedInteger('mileage')->default(0);
            $t->enum('fuel_type', ['pertalite', 'pertamax', 'premium', 'diesel', 'electric']);
            $t->enum('transmission', ['manual', 'automatic']);
            $t->unsignedTinyInteger('seat_count')->default(5);
            $t->unsignedSmallInteger('engine_cc')->nullable();
            $t->decimal('daily_rate', 12, 2);
            $t->decimal('weekly_rate', 12, 2);
            $t->decimal('monthly_rate', 12, 2);
            $t->decimal('late_fee_per_hour', 12, 2)->default(0);
            $t->decimal('late_fee_per_day', 12, 2)->default(0);
            $t->decimal('deposit_amount', 12, 2)->default(0);
            $t->enum('status', ['available', 'reserved', 'preparing', 'rented', 'overdue', 'inspection', 'cleaning', 'maintenance', 'damaged', 'inactive'])->default('available');
            $t->json('features')->nullable();
            $t->string('photo_url')->nullable();
            $t->boolean('is_active')->default(true);
            $t->boolean('is_insured')->default(false);
            $t->timestamp('last_serviced_at')->nullable();
            $t->timestamp('last_km_at')->nullable();
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
            $t->softDeletes();

            $t->index('category_id');
            $t->index('brand_id');
            $t->index('location_id');
            $t->index('status');
            $t->index('is_active');
        });

        Schema::create('vehicle_photos', function (Blueprint $t) {
            $t->id();
            $t->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $t->string('photo_url');
            $t->string('caption')->nullable();
            $t->unsignedSmallInteger('sort_order')->default(0);
            $t->boolean('is_primary')->default(false);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_photos');
        Schema::dropIfExists('vehicles');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('logo')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('address');
            $table->string('city');
            $table->string('province');
            $table->string('phone', 20);
            $table->string('email')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->json('operating_hours')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained();
            $table->foreignId('brand_id')->constrained();
            $table->foreignId('location_id')->constrained();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('plate_number')->unique();
            $table->integer('year');
            $table->string('color');
            $table->integer('mileage')->default(0);
            $table->enum('fuel_type', ['pertalite', 'pertamax', 'premium', 'diesel', 'electric']);
            $table->enum('transmission', ['manual', 'automatic']);
            $table->integer('seat_count')->default(5);
            $table->decimal('daily_rate', 12, 2);
            $table->decimal('weekly_rate', 12, 2);
            $table->decimal('monthly_rate', 12, 2);
            $table->decimal('deposit_amount', 12, 2)->default(0);
            $table->decimal('late_fee_per_hour', 12, 2)->default(0);
            $table->decimal('late_fee_per_day', 12, 2)->default(0);
            $table->enum('status', ['available', 'rented', 'maintenance', 'reserved', 'retired'])->default('available');
            $table->json('features')->nullable();
            $table->string('photo_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('vehicle_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->string('photo_url');
            $table->string('caption')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
        });

        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone', 20);
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->string('ktp_number')->nullable()->unique();
            $table->string('sim_number')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->string('company_name')->nullable();
            $table->string('company_npwp')->nullable();
            $table->integer('trust_score')->default(80);
            $table->integer('total_orders')->default(0);
            $table->decimal('total_spent', 15, 2)->default(0);
            $table->enum('loyalty_tier', ['bronze', 'silver', 'gold', 'platinum'])->default('bronze');
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('customer_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->enum('document_type', [
                'ktp',
                'sim_selfie',
                'sim_back',
                'npwp',
                'company_contract',
                'selfie',
                'other',
            ]);
            $table->string('document_url');
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone', 20);
            $table->string('email')->nullable();
            $table->string('sim_number');
            $table->enum('sim_type', ['A', 'B', 'C', 'D']);
            $table->date('sim_expiry');
            $table->text('address')->nullable();
            $table->decimal('rating', 3, 2)->default(5.00);
            $table->integer('total_trips')->default(0);
            $table->boolean('is_active')->default(true);
            $table->string('photo_url')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->enum('type', [
                'bank_transfer',
                'cash',
                'e_wallet',
                'credit_card',
                'debit_card',
                'qris',
                'virtual_account',
            ]);
            $table->string('icon')->nullable();
            $table->text('instructions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('addons', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->enum('price_type', ['fixed', 'daily', 'percentage']);
            $table->decimal('price', 12, 2)->default(0);
            $table->string('icon')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('insurance_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained();
            $table->string('provider_name');
            $table->string('policy_number');
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('coverage_type', ['comprehensive', 'partial', 'total_loss_only']);
            $table->decimal('premium', 12, 2);
            $table->decimal('max_claim', 12, 2);
            $table->enum('status', ['active', 'expired', 'cancelled'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('spare_parts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('part_number')->nullable()->unique();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('stock')->default(0);
            $table->integer('min_stock')->default(5);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->string('location_in_store')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spare_parts');
        Schema::dropIfExists('insurance_policies');
        Schema::dropIfExists('addons');
        Schema::dropIfExists('payment_methods');
        Schema::dropIfExists('drivers');
        Schema::dropIfExists('customer_documents');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('vehicle_photos');
        Schema::dropIfExists('vehicles');
        Schema::dropIfExists('locations');
        Schema::dropIfExists('brands');
        Schema::dropIfExists('categories');
    }
};

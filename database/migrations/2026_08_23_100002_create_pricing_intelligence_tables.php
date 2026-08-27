<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demand_forecasts', function (Blueprint $t) {
            $t->id();
            $t->date('forecast_date')->index();
            $t->foreignId('category_id')->nullable()->constrained()->cascadeOnDelete();
            $t->foreignId('location_id')->nullable()->constrained()->cascadeOnDelete();
            $t->decimal('predicted_occupancy', 5, 4)->default(0);
            $t->decimal('confidence', 5, 2)->default(0);
            $t->json('factors')->nullable();
            $t->timestamps();
            $t->unique(['forecast_date', 'category_id', 'location_id'], 'demand_forecasts_unique_scope');
        });

        Schema::create('competitor_prices', function (Blueprint $t) {
            $t->id();
            $t->string('competitor_name');
            $t->foreignId('category_id')->nullable()->constrained()->cascadeOnDelete();
            $t->string('city')->nullable()->index();
            $t->decimal('daily_rate', 14, 2);
            $t->date('observed_at');
            $t->string('source_url')->nullable();
            $t->text('notes')->nullable();
            $t->timestamps();
        });

        Schema::create('early_bird_rules', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->unsignedSmallInteger('min_lead_days')->default(7);
            $t->unsignedSmallInteger('max_lead_days')->nullable();
            $t->enum('discount_type', ['percentage', 'fixed'])->default('percentage');
            $t->decimal('discount_value', 10, 2);
            $t->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('location_id')->nullable()->constrained()->nullOnDelete();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('flash_sales', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->enum('discount_type', ['percentage', 'fixed'])->default('percentage');
            $t->decimal('discount_value', 10, 2);
            $t->timestamp('starts_at');
            $t->timestamp('ends_at');
            $t->unsignedInteger('max_redemptions')->nullable();
            $t->unsignedInteger('used_count')->default(0);
            $t->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('location_id')->nullable()->constrained()->nullOnDelete();
            $t->json('vehicle_ids')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::table('customers', fn (Blueprint $t) => $t->unsignedInteger('loyalty_points')->default(0)->after('loyalty_tier'));

        Schema::create('loyalty_ledgers', function (Blueprint $t) {
            $t->id();
            $t->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $t->enum('type', ['earn', 'redeem', 'adjust', 'expire']);
            $t->integer('points');
            $t->integer('balance_after');
            $t->string('description')->nullable();
            $t->nullableMorphs('reference');
            $t->timestamp('expires_at')->nullable();
            $t->timestamps();
        });

        Schema::create('referrals', function (Blueprint $t) {
            $t->id();
            $t->foreignId('referrer_customer_id')->constrained('customers')->cascadeOnDelete();
            $t->string('code', 32)->unique();
            $t->string('referred_email')->nullable();
            $t->string('referred_name')->nullable();
            $t->foreignId('referred_customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $t->enum('reward_type', ['points', 'credit'])->default('points');
            $t->unsignedInteger('reward_value')->default(50);
            $t->enum('status', ['pending', 'registered', 'first_order', 'rewarded', 'rejected'])->default('pending');
            $t->timestamp('completed_at')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referrals');
        Schema::dropIfExists('loyalty_ledgers');
        Schema::table('customers', fn (Blueprint $t) => $t->dropColumn('loyalty_points'));
        Schema::dropIfExists('flash_sales');
        Schema::dropIfExists('early_bird_rules');
        Schema::dropIfExists('competitor_prices');
        Schema::dropIfExists('demand_forecasts');
    }
};

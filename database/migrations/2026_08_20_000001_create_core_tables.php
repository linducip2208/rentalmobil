<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('locations', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('slug')->unique();
            $t->string('address')->nullable();
            $t->string('city')->nullable();
            $t->string('province')->nullable();
            $t->string('postal_code')->nullable();
            $t->string('phone')->nullable();
            $t->string('email')->nullable();
            $t->decimal('latitude', 10, 7)->nullable();
            $t->decimal('longitude', 10, 7)->nullable();
            $t->json('operating_hours')->nullable();
            $t->boolean('is_headquarters')->default(false);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('categories', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('slug')->unique();
            $t->text('description')->nullable();
            $t->string('icon')->nullable();
            $t->integer('sort_order')->default(0);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('brands', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('slug')->unique();
            $t->string('logo')->nullable();
            $t->string('country')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('drivers', function (Blueprint $t) {
            $t->id();
            $t->foreignId('location_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->string('phone');
            $t->string('email')->nullable();
            $t->string('address')->nullable();
            $t->string('ktp_number')->nullable();
            $t->string('sim_type')->comment('A, B1, B2, C');
            $t->string('sim_number')->nullable();
            $t->date('sim_expiry')->nullable();
            $t->string('photo')->nullable();
            $t->decimal('rating', 3, 2)->default(0);
            $t->integer('total_trips')->default(0);
            $t->boolean('is_available')->default(true);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
            $t->softDeletes();
        });

        Schema::create('payment_methods', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('code')->unique();
            $t->string('type')->comment('bank_transfer, cash, e_wallet, credit_card, debit_card, qris, virtual_account');
            $t->string('icon')->nullable();
            $t->json('provider_config')->nullable();
            $t->decimal('additional_fee', 15, 2)->default(0);
            $t->integer('sort_order')->default(0);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('addons', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('slug')->unique();
            $t->text('description')->nullable();
            $t->decimal('price', 15, 2)->default(0);
            $t->string('price_type')->default('fixed')->comment('fixed, daily, percentage');
            $t->string('icon')->nullable();
            $t->boolean('requires_driver')->default(false);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('addons');
        Schema::dropIfExists('payment_methods');
        Schema::dropIfExists('drivers');
        Schema::dropIfExists('brands');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('locations');
    }
};

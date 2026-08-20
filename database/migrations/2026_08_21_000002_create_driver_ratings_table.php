<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('driver_ratings', function (Blueprint $t) {
            $t->id();
            $t->foreignId('driver_id')->constrained('drivers')->restrictOnDelete();
            $t->foreignId('rental_order_id')->nullable()->constrained('rental_orders')->restrictOnDelete();
            $t->foreignId('customer_id')->nullable()->constrained('customers')->restrictOnDelete();
            $t->unsignedTinyInteger('rating')->comment('1-5');
            $t->unsignedTinyInteger('punctuality')->nullable()->comment('1-5');
            $t->unsignedTinyInteger('driving_skill')->nullable()->comment('1-5');
            $t->unsignedTinyInteger('attitude')->nullable()->comment('1-5');
            $t->unsignedTinyInteger('vehicle_cleanliness')->nullable()->comment('1-5');
            $t->text('comment')->nullable();
            $t->boolean('is_anonymous')->default(false);
            $t->timestamps();

            $t->index('driver_id');
            $t->index('rating');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_ratings');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trip_permits', function (Blueprint $t) {
            $t->id();
            $t->string('spj_number')->unique();
            $t->foreignId('rental_order_id')->constrained()->restrictOnDelete();
            $t->foreignId('driver_id')->constrained()->restrictOnDelete();
            $t->string('route_from');
            $t->string('route_to');
            $t->enum('fuel_start_level', ['full', 'three_quarter', 'half', 'quarter', 'empty'])->default('full');
            $t->enum('fuel_end_level', ['full', 'three_quarter', 'half', 'quarter', 'empty'])->nullable();
            $t->unsignedInteger('odometer_start')->nullable();
            $t->unsignedInteger('odometer_end')->nullable();
            $t->decimal('toll_cost', 12, 2)->default(0);
            $t->decimal('parking_cost', 12, 2)->default(0);
            $t->decimal('accommodation_cost', 12, 2)->default(0)->comment('Akomodasi/uang makan luar kota');
            $t->text('notes')->nullable();
            $t->enum('status', ['open', 'closed'])->default('open');
            $t->timestamp('started_at')->nullable();
            $t->timestamp('finished_at')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trip_permits');
    }
};

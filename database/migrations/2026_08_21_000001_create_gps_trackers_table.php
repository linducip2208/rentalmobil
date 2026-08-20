<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gps_trackers', function (Blueprint $t) {
            $t->id();
            $t->foreignId('vehicle_id')->nullable()->constrained('vehicles')->restrictOnDelete();
            $t->string('device_id')->unique();
            $t->string('device_name')->nullable();
            $t->string('brand')->nullable()->comment('Realme, iTech,Concox, dll');
            $t->string('model')->nullable();
            $t->string('sim_card_number')->nullable();
            $t->string('sim_provider')->nullable()->comment('Telkomsel, XL, dll');
            $t->enum('status', ['active', 'inactive', 'maintenance', 'lost'])->default('active');
            $t->boolean('is_active')->default(true);
            $t->decimal('last_latitude', 10, 7)->nullable();
            $t->decimal('last_longitude', 10, 7)->nullable();
            $t->decimal('last_speed', 6, 2)->nullable();
            $t->unsignedSmallInteger('last_heading')->nullable();
            $t->unsignedTinyInteger('last_battery_level')->nullable();
            $t->timestamp('last_update_at')->nullable();
            $t->timestamp('installed_at')->nullable();
            $t->text('notes')->nullable();
            $t->json('metadata')->nullable();
            $t->timestamps();
            $t->softDeletes();

            $t->index('vehicle_id');
            $t->index('device_id');
            $t->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gps_trackers');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fuel_anomalies', function (Blueprint $t) {
            $t->id();
            $t->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $t->foreignId('fuel_log_id')->constrained()->cascadeOnDelete();
            $t->decimal('distance_km', 10, 2)->default(0);
            $t->decimal('expected_liters', 8, 2);
            $t->decimal('actual_liters', 8, 2);
            $t->decimal('baseline_km_per_liter', 8, 2)->default(0);
            $t->decimal('actual_km_per_liter', 8, 2)->default(0);
            $t->decimal('deviation_pct', 6, 2)->default(0);
            $t->enum('status', ['open', 'reviewed', 'confirmed_theft', 'false_alarm'])->default('open');
            $t->text('notes')->nullable();
            $t->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
        });

        Schema::create('driver_scorecards', function (Blueprint $t) {
            $t->id();
            $t->foreignId('driver_id')->constrained()->cascadeOnDelete();
            $t->date('period_start');
            $t->date('period_end');
            $t->unsignedInteger('overspeed_count')->default(0);
            $t->unsignedInteger('harsh_brake_count')->default(0);
            $t->unsignedInteger('harsh_acceleration_count')->default(0);
            $t->unsignedInteger('long_idle_count')->default(0);
            $t->unsignedInteger('geofence_violation_count')->default(0);
            $t->unsignedInteger('trips')->default(0);
            $t->decimal('avg_rating', 3, 2)->default(0);
            $t->decimal('score', 5, 2)->default(0);
            $t->unsignedInteger('rank_position')->nullable();
            $t->timestamps();
            $t->unique(['driver_id', 'period_start', 'period_end'], 'driver_scorecards_unique_period');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_scorecards');
        Schema::dropIfExists('fuel_anomalies');
    }
};

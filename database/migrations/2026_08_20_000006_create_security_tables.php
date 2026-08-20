<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blacklist_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('id_number')->nullable();
            $table->string('phone', 20)->nullable();
            $table->text('reason');
            $table->enum('level', ['warning', 'restricted', 'blocked', 'legal'])->default('warning');
            $table->json('evidence')->nullable();
            $table->foreignId('added_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('trust_score_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained();
            $table->integer('previous_score');
            $table->integer('new_score');
            $table->string('change_reason');
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('watch_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained();
            $table->text('reason');
            $table->enum('severity', ['low', 'medium', 'high'])->default('low');
            $table->foreignId('added_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('investigation_cases', function (Blueprint $table) {
            $table->id();
            $table->string('case_number')->unique();
            $table->string('title');
            $table->text('description');
            $table->foreignId('vehicle_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('rental_order_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('status', ['open', 'in_progress', 'resolved', 'closed'])->default('open');
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->json('evidence')->nullable();
            $table->text('resolution')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('police_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('investigation_case_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('vehicle_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('rental_order_id')->nullable()->constrained()->nullOnDelete();
            $table->string('report_number')->nullable();
            $table->string('police_station');
            $table->string('officer_name')->nullable();
            $table->date('report_date');
            $table->text('report_text');
            $table->enum('status', ['filed', 'investigating', 'resolved', 'no_action'])->default('filed');
            $table->json('documents')->nullable();
            $table->timestamps();
        });

        Schema::create('gps_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained();
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->decimal('speed', 6, 2)->nullable();
            $table->integer('heading')->nullable();
            $table->decimal('accuracy', 8, 2)->nullable();
            $table->integer('battery_level')->nullable();
            $table->timestamp('recorded_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gps_logs');
        Schema::dropIfExists('police_reports');
        Schema::dropIfExists('investigation_cases');
        Schema::dropIfExists('watch_lists');
        Schema::dropIfExists('trust_score_logs');
        Schema::dropIfExists('blacklist_entries');
    }
};

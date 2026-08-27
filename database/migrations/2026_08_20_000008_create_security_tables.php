<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('investigation_cases', function (Blueprint $t) {
            $t->id();
            $t->string('case_number')->unique()->index();
            $t->foreignId('vehicle_id')->nullable()->constrained('vehicles')->restrictOnDelete();
            $t->foreignId('customer_id')->nullable()->constrained('customers')->restrictOnDelete();
            $t->foreignId('rental_order_id')->nullable()->constrained('rental_orders')->restrictOnDelete();
            $t->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $t->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            $t->enum('status', ['open', 'in_progress', 'resolved', 'closed'])->default('open');
            $t->string('title');
            $t->text('description')->nullable();
            $t->json('evidence')->nullable();
            $t->text('resolution')->nullable();
            $t->timestamp('resolved_at')->nullable();
            $t->timestamps();
        });

        Schema::create('police_reports', function (Blueprint $t) {
            $t->id();
            $t->foreignId('investigation_case_id')->nullable()->constrained('investigation_cases')->restrictOnDelete();
            $t->foreignId('vehicle_id')->nullable()->constrained('vehicles')->restrictOnDelete();
            $t->foreignId('rental_order_id')->nullable()->constrained('rental_orders')->restrictOnDelete();
            $t->string('report_number')->nullable();
            $t->string('police_station')->nullable();
            $t->string('officer_name')->nullable();
            $t->date('report_date')->nullable();
            $t->text('report_text')->nullable();
            $t->enum('status', ['filed', 'investigating', 'resolved', 'no_action'])->default('filed');
            $t->json('documents')->nullable();
            $t->timestamps();
        });

        Schema::create('blacklist_entries', function (Blueprint $t) {
            $t->id();
            $t->foreignId('customer_id')->nullable()->constrained('customers')->restrictOnDelete();
            $t->string('name')->nullable();
            $t->string('id_number')->nullable();
            $t->string('phone')->nullable();
            $t->string('reason');
            $t->enum('level', ['warning', 'restricted', 'blocked', 'legal'])->default('warning');
            $t->json('evidence')->nullable();
            $t->foreignId('added_by')->nullable()->constrained('users')->nullOnDelete();
            $t->boolean('is_active')->default(true);
            $t->timestamp('expires_at')->nullable();
            $t->timestamps();
        });

        Schema::create('watch_lists', function (Blueprint $t) {
            $t->id();
            $t->foreignId('vehicle_id')->nullable()->constrained('vehicles')->restrictOnDelete();
            $t->string('reason');
            $t->enum('severity', ['low', 'medium', 'high'])->default('medium');
            $t->boolean('is_active')->default(true);
            $t->foreignId('added_by')->nullable()->constrained('users')->nullOnDelete();
            $t->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('resolved_at')->nullable();
            $t->timestamps();
        });

        Schema::create('trust_score_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('customer_id')->constrained('customers')->restrictOnDelete();
            $t->unsignedTinyInteger('previous_score');
            $t->unsignedTinyInteger('new_score');
            $t->text('change_reason')->nullable();
            $t->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
        });

        Schema::create('login_attempts', function (Blueprint $t) {
            $t->id();
            $t->string('email');
            $t->string('ip_address');
            $t->string('user_agent')->nullable();
            $t->boolean('successful')->default(false);
            $t->timestamp('attempted_at');
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('login_attempts');
        Schema::dropIfExists('trust_score_logs');
        Schema::dropIfExists('watch_lists');
        Schema::dropIfExists('blacklist_entries');
        Schema::dropIfExists('police_reports');
        Schema::dropIfExists('investigation_cases');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gps_logs', function (Blueprint $table) {
            $table->foreignId('gps_tracker_id')->nullable()->after('vehicle_id')->constrained('gps_trackers')->nullOnDelete();
            $table->string('external_event_id')->nullable()->after('gps_tracker_id');
            $table->string('payload_hash', 64)->nullable()->after('external_event_id');
            $table->unique(['gps_tracker_id', 'external_event_id'], 'gps_log_external_event_unique');
            $table->unique(['gps_tracker_id', 'payload_hash'], 'gps_log_payload_hash_unique');
        });

        Schema::table('gps_integrations', function (Blueprint $table) {
            $table->unsignedSmallInteger('failure_count')->default(0)->after('last_error');
            $table->timestamp('health_checked_at')->nullable()->after('failure_count');
            $table->string('health_status')->default('unknown')->after('health_checked_at');
        });

        Schema::create('gps_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gps_tracker_id')->constrained('gps_trackers')->cascadeOnDelete();
            $table->foreignId('vehicle_id')->constrained('vehicles')->restrictOnDelete();
            $table->enum('type', ['overspeed', 'geofence_exit', 'offline', 'low_battery', 'provider_failure']);
            $table->enum('severity', ['info', 'warning', 'critical'])->default('warning');
            $table->string('deduplication_key')->unique();
            $table->string('title');
            $table->text('message');
            $table->json('context')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamp('acknowledged_at')->nullable();
            $table->foreignId('acknowledged_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('acknowledgement_note')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            $table->index(['type', 'acknowledged_at', 'occurred_at']);
        });

        Schema::create('gps_commands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gps_tracker_id')->constrained('gps_trackers')->restrictOnDelete();
            $table->foreignId('requested_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('command_name');
            $table->json('parameters')->nullable();
            $table->enum('status', ['pending_approval', 'approved', 'rejected', 'queued', 'sent', 'failed'])->default('pending_approval');
            $table->text('reason');
            $table->text('review_note')->nullable();
            $table->string('idempotency_key')->unique();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->text('response_body')->nullable();
            $table->timestamps();
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gps_commands');
        Schema::dropIfExists('gps_alerts');
        Schema::table('gps_integrations', fn (Blueprint $table) => $table->dropColumn(['failure_count', 'health_checked_at', 'health_status']));
        Schema::table('gps_logs', function (Blueprint $table) {
            $table->dropUnique('gps_log_external_event_unique');
            $table->dropUnique('gps_log_payload_hash_unique');
            $table->dropConstrainedForeignId('gps_tracker_id');
            $table->dropColumn(['external_event_id', 'payload_hash']);
        });
    }
};

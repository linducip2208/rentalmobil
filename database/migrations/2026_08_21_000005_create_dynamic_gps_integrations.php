<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gps_integrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->unique()->constrained('providers')->cascadeOnDelete();
            $table->string('adapter_format');
            $table->string('auth_type')->default('bearer');
            $table->string('credential_key_name')->nullable();
            $table->text('credential_secret_encrypted')->nullable();
            $table->string('devices_endpoint')->nullable();
            $table->string('positions_endpoint')->nullable();
            $table->string('events_endpoint')->nullable();
            $table->string('commands_endpoint')->nullable();
            $table->string('http_method', 10)->default('GET');
            $table->json('request_parameters')->nullable();
            $table->json('field_mapping');
            $table->json('response_paths')->nullable();
            $table->string('webhook_identifier_field')->nullable();
            $table->text('webhook_secret_encrypted')->nullable();
            $table->string('webhook_signature_header')->nullable();
            $table->unsignedSmallInteger('poll_interval_minutes')->default(5);
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamp('last_success_at')->nullable();
            $table->text('last_error')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['adapter_format', 'is_active']);
        });

        Schema::table('gps_trackers', function (Blueprint $table) {
            $table->foreignId('gps_integration_id')->nullable()->after('vehicle_id')->constrained('gps_integrations')->nullOnDelete();
            $table->string('external_device_id')->nullable()->after('device_id');
            $table->unique(['gps_integration_id', 'external_device_id'], 'gps_tracker_external_unique');
        });
    }

    public function down(): void
    {
        Schema::table('gps_trackers', function (Blueprint $table) {
            $table->dropUnique('gps_tracker_external_unique');
            $table->dropConstrainedForeignId('gps_integration_id');
            $table->dropColumn('external_device_id');
        });
        Schema::dropIfExists('gps_integrations');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notification_queue', function (Blueprint $table) {
            $table->foreignId('provider_id')->nullable()->after('id')->constrained('providers')->nullOnDelete();
            $table->foreignId('template_id')->nullable()->after('provider_id')->constrained('notification_templates')->nullOnDelete();
            $table->string('subject')->nullable()->after('channel');
            $table->text('body')->nullable()->after('subject');
        });

        Schema::table('gps_trackers', function (Blueprint $table) {
            $table->char('ingest_token_hash', 64)->nullable()->unique()->after('device_id');
            $table->unsignedSmallInteger('speed_limit_kmh')->nullable()->after('last_speed');
            $table->decimal('geofence_latitude', 10, 7)->nullable()->after('last_longitude');
            $table->decimal('geofence_longitude', 10, 7)->nullable()->after('geofence_latitude');
            $table->unsignedInteger('geofence_radius_m')->nullable()->after('geofence_longitude');
        });
    }

    public function down(): void
    {
        Schema::table('gps_trackers', function (Blueprint $table) {
            $table->dropUnique(['ingest_token_hash']);
            $table->dropColumn(['ingest_token_hash', 'speed_limit_kmh', 'geofence_latitude', 'geofence_longitude', 'geofence_radius_m']);
        });

        Schema::table('notification_queue', function (Blueprint $table) {
            $table->dropConstrainedForeignId('template_id');
            $table->dropConstrainedForeignId('provider_id');
            $table->dropColumn(['subject', 'body']);
        });
    }
};

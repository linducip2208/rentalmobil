<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Additive, backward-compatible image metadata for the vehicle gallery.
     */
    public function up(): void
    {
        Schema::table('vehicle_photos', function (Blueprint $table) {
            $table->string('alt_text')->nullable()->after('caption');
            $table->string('type', 30)->default('exterior')->after('alt_text');
            $table->string('disk', 30)->default('public')->after('type');
        });
    }

    public function down(): void
    {
        Schema::table('vehicle_photos', function (Blueprint $table) {
            $table->dropColumn(['alt_text', 'type', 'disk']);
        });
    }
};

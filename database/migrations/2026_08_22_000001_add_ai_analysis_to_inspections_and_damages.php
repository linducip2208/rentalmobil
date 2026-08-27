<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicle_inspections', function (Blueprint $t) {
            $t->enum('ai_status', ['pending', 'processing', 'done', 'failed'])->nullable()->after('result');
            $t->json('ai_analysis')->nullable()->after('ai_status');
            $t->timestamp('ai_analyzed_at')->nullable()->after('ai_analysis');
        });

        Schema::table('damage_reports', function (Blueprint $t) {
            $t->json('ai_findings')->nullable()->after('photos');
            $t->decimal('ai_confidence', 5, 2)->nullable()->after('ai_findings');
        });
    }

    public function down(): void
    {
        Schema::table('vehicle_inspections', function (Blueprint $t) {
            $t->dropColumn(['ai_status', 'ai_analysis', 'ai_analyzed_at']);
        });
        Schema::table('damage_reports', function (Blueprint $t) {
            $t->dropColumn(['ai_findings', 'ai_confidence']);
        });
    }
};

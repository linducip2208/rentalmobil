<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('handover_records', fn (Blueprint $t) => $t->json('video_urls')->nullable());

        Schema::create('damage_comparisons', function (Blueprint $t) {
            $t->id();
            $t->foreignId('rental_order_id')->constrained()->cascadeOnDelete();
            $t->foreignId('checkout_handover_id')->nullable()->constrained('handover_records')->nullOnDelete();
            $t->foreignId('return_handover_id')->nullable()->constrained('handover_records')->nullOnDelete();
            $t->foreignId('provider_id')->nullable()->constrained()->nullOnDelete();
            $t->json('analysis')->nullable();
            $t->unsignedInteger('new_damages_count')->default(0);
            $t->decimal('estimated_cost', 14, 2)->default(0);
            $t->enum('status', ['pending', 'completed', 'failed'])->default('pending');
            $t->timestamp('completed_at')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('damage_comparisons');
        Schema::table('handover_records', fn (Blueprint $t) => $t->dropColumn('video_urls'));
    }
};

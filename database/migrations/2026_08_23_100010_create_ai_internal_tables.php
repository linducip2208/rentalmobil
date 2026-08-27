<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_chat_messages', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->enum('role', ['user', 'assistant', 'system']);
            $t->text('content');
            $t->unsignedInteger('tokens_used')->default(0);
            $t->unsignedInteger('latency_ms')->default(0);
            $t->string('model')->nullable();
            $t->foreignId('provider_id')->nullable()->constrained()->nullOnDelete();
            $t->timestamps();
        });

        Schema::create('document_ocr_results', function (Blueprint $t) {
            $t->id();
            $t->nullableMorphs('documentable');
            $t->foreignId('provider_id')->nullable()->constrained()->nullOnDelete();
            $t->string('document_kind', 40)->default('stnk');
            $t->json('extracted')->nullable();
            $t->decimal('confidence', 5, 2)->default(0);
            $t->enum('status', ['pending', 'completed', 'failed'])->default('pending');
            $t->json('raw_response')->nullable();
            $t->timestamps();
        });

        Schema::create('dispatch_recommendations', function (Blueprint $t) {
            $t->id();
            $t->foreignId('delivery_id')->constrained()->cascadeOnDelete();
            $t->foreignId('recommended_driver_id')->nullable()->constrained('drivers')->nullOnDelete();
            $t->foreignId('recommended_vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();
            $t->decimal('score', 5, 2)->default(0);
            $t->json('reasons')->nullable();
            $t->enum('status', ['suggested', 'accepted', 'dismissed'])->default('suggested');
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dispatch_recommendations');
        Schema::dropIfExists('document_ocr_results');
        Schema::dropIfExists('ai_chat_messages');
    }
};

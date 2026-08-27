<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('webhook_deliveries', function (Blueprint $t) {
            $t->id();
            $t->foreignId('webhook_id')->constrained()->cascadeOnDelete();
            $t->string('event');
            $t->json('payload');
            $t->enum('status', ['pending', 'delivered', 'failed'])->default('pending')->index();
            $t->unsignedSmallInteger('attempts')->default(0);
            $t->unsignedSmallInteger('max_attempts')->default(5);
            $t->unsignedInteger('response_code')->nullable();
            $t->text('response_body')->nullable();
            $t->text('error_note')->nullable();
            $t->timestamp('delivered_at')->nullable();
            $t->timestamp('next_retry_at')->nullable()->index();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_deliveries');
    }
};

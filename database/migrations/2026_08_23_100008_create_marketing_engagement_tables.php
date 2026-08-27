<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('wa_conversations', function (Blueprint $t) {
            $t->id();
            $t->string('phone', 30)->unique();
            $t->string('name')->nullable();
            $t->string('state', 40)->default('greeting');
            $t->json('context')->nullable();
            $t->boolean('is_handed_over')->default(false);
            $t->timestamp('last_message_at')->index();
            $t->timestamps();
        });

        Schema::create('wa_messages', function (Blueprint $t) {
            $t->id();
            $t->foreignId('wa_conversation_id')->constrained('wa_conversations')->cascadeOnDelete();
            $t->enum('direction', ['inbound', 'outbound']);
            $t->text('body')->nullable();
            $t->json('payload')->nullable();
            $t->timestamp('processed_at')->nullable();
            $t->timestamps();
        });

        Schema::create('external_reviews', function (Blueprint $t) {
            $t->id();
            $t->enum('platform', ['google', 'maps', 'tripadvisor', 'whatsapp', 'manual'])->default('google');
            $t->string('external_id')->nullable()->index();
            $t->string('author_name')->nullable();
            $t->unsignedTinyInteger('rating')->default(5);
            $t->text('content')->nullable();
            $t->date('review_date')->nullable();
            $t->boolean('is_featured')->default(false);
            $t->string('import_batch', 60)->nullable()->index();
            $t->foreignId('imported_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('external_reviews');
        Schema::dropIfExists('wa_messages');
        Schema::dropIfExists('wa_conversations');
    }
};

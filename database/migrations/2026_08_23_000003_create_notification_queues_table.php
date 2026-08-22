<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('notification_queues')) {
            return;
        }

        Schema::create('notification_queues', function (Blueprint $t) {
            $t->id();
            $t->nullableMorphs('notifiable');
            $t->foreignId('provider_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('template_id')->nullable()->constrained('notification_templates')->nullOnDelete();
            $t->string('event_type')->nullable()->index();
            $t->enum('channel', ['whatsapp', 'sms', 'email', 'telegram', 'push'])->default('whatsapp');
            $t->string('subject')->nullable();
            $t->text('body')->nullable();
            $t->json('payload')->nullable();
            $t->enum('status', ['pending', 'sending', 'sent', 'failed'])->default('pending')->index();
            $t->timestamp('scheduled_at')->nullable()->index();
            $t->timestamp('sent_at')->nullable();
            $t->timestamp('failed_at')->nullable();
            $t->text('error_message')->nullable();
            $t->unsignedSmallInteger('attempts')->default(0);
            $t->unsignedSmallInteger('max_attempts')->default(3);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_queues');
    }
};

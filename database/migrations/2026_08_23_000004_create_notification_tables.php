<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('notification_templates')) {
            Schema::create('notification_templates', function (Blueprint $t) {
                $t->id();
                $t->foreignId('provider_id')->nullable()->constrained()->nullOnDelete();
                $t->string('name');
                $t->string('event_type')->index();
                $t->enum('channel', ['sms', 'whatsapp', 'email', 'push'])->default('whatsapp');
                $t->string('subject')->nullable();
                $t->text('body');
                $t->json('variables')->nullable();
                $t->boolean('is_active')->default(true);
                $t->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_templates');
    }
};

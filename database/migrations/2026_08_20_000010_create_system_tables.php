<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('providers', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->enum('type', ['payment', 'sms', 'whatsapp', 'gps', 'storage', 'ai']);
            $t->string('api_format')->nullable();
            $t->string('base_url')->nullable();
            $t->text('api_key_encrypted')->nullable();
            $t->json('extra_headers')->nullable();
            $t->json('config')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('notification_templates', function (Blueprint $t) {
            $t->id();
            $t->foreignId('provider_id')->nullable()->constrained('providers')->restrictOnDelete();
            $t->string('name');
            $t->string('event_type');
            $t->enum('channel', ['sms', 'whatsapp', 'email', 'push']);
            $t->string('subject')->nullable();
            $t->text('body');
            $t->json('variables')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('notification_queue', function (Blueprint $t) {
            $t->id();
            $t->string('notifiable_type')->nullable();
            $t->unsignedBigInteger('notifiable_id')->nullable();
            $t->string('event_type');
            $t->enum('channel', ['sms', 'whatsapp', 'email', 'push']);
            $t->json('payload')->nullable();
            $t->enum('status', ['pending', 'sending', 'sent', 'failed'])->default('pending');
            $t->timestamp('scheduled_at')->nullable();
            $t->timestamp('sent_at')->nullable();
            $t->timestamp('failed_at')->nullable();
            $t->text('error_message')->nullable();
            $t->unsignedSmallInteger('attempts')->default(0);
            $t->unsignedSmallInteger('max_attempts')->default(3);
            $t->timestamps();

            $t->index(['notifiable_type', 'notifiable_id']);
        });

        Schema::create('webhooks', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('url');
            $t->string('secret')->nullable();
            $t->json('events');
            $t->boolean('is_active')->default(true);
            $t->timestamp('last_triggered_at')->nullable();
            $t->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $t->string('action');
            $t->string('auditable_type');
            $t->unsignedBigInteger('auditable_id');
            $t->json('old_values')->nullable();
            $t->json('new_values')->nullable();
            $t->string('url')->nullable();
            $t->string('ip_address')->nullable();
            $t->string('user_agent')->nullable();
            $t->foreignId('branch_id')->nullable()->constrained('locations')->restrictOnDelete();
            $t->timestamps();

            $t->index(['auditable_type', 'auditable_id']);
            $t->index('user_id');
            $t->index('action');
            $t->index('branch_id');
        });

        Schema::create('system_settings', function (Blueprint $t) {
            $t->id();
            $t->string('group_name');
            $t->string('key')->unique();
            $t->text('value')->nullable();
            $t->enum('type', ['string', 'integer', 'boolean', 'json', 'text', 'decimal'])->default('string');
            $t->text('description')->nullable();
            $t->timestamps();
        });

        Schema::create('secure_tokens', function (Blueprint $t) {
            $t->id();
            $t->string('token_hash')->unique()->index();
            $t->enum('scope', [
                'quotation_view', 'quotation_approve', 'document_upload', 'contract_sign',
                'invoice_view', 'receipt_download', 'deposit_approval', 'survey',
            ]);
            $t->string('reference_type');
            $t->unsignedBigInteger('reference_id');
            $t->timestamp('expires_at');
            $t->timestamp('revoked_at')->nullable();
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->json('metadata')->nullable();
            $t->timestamps();
        });

        Schema::create('document_templates', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->enum('type', ['quotation', 'contract', 'handover', 'invoice', 'receipt', 'other']);
            $t->longText('content');
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('redirects', function (Blueprint $t) {
            $t->id();
            $t->string('from_url')->unique();
            $t->string('to_url');
            $t->unsignedSmallInteger('status_code')->default(301);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('sitemap_entries', function (Blueprint $t) {
            $t->id();
            $t->string('url');
            $t->decimal('priority', 3, 2)->default(0.50);
            $t->string('change_frequency')->nullable();
            $t->date('last_mod')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('approval_workflows', function (Blueprint $t) {
            $t->id();
            $t->enum('type', [
                'discount', 'refund', 'deposit_deduction', 'expense',
                'maintenance', 'purchase_order',
            ]);
            $t->string('reference_type');
            $t->unsignedBigInteger('reference_id');
            $t->foreignId('requested_by')->constrained('users')->restrictOnDelete();
            $t->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $t->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $t->decimal('amount', 14, 2)->nullable();
            $t->text('reason')->nullable();
            $t->text('notes')->nullable();
            $t->timestamp('approved_at')->nullable();
            $t->timestamp('rejected_at')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_workflows');
        Schema::dropIfExists('sitemap_entries');
        Schema::dropIfExists('redirects');
        Schema::dropIfExists('document_templates');
        Schema::dropIfExists('secure_tokens');
        Schema::dropIfExists('system_settings');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('webhooks');
        Schema::dropIfExists('notification_queue');
        Schema::dropIfExists('notification_templates');
        Schema::dropIfExists('providers');
    }
};

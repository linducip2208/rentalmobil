<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('face_verifications', function (Blueprint $t) {
            $t->id();
            $t->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $t->string('ktp_photo_url')->nullable();
            $t->string('selfie_url')->nullable();
            $t->foreignId('provider_id')->nullable()->constrained()->nullOnDelete();
            $t->decimal('match_score', 5, 2)->default(0);
            $t->enum('status', ['pending', 'matched', 'mismatch', 'failed'])->default('pending');
            $t->string('context', 50)->default('booking');
            $t->json('analysis')->nullable();
            $t->timestamp('checked_at')->nullable();
            $t->timestamps();
        });

        Schema::create('fraud_patterns', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->enum('pattern_type', ['duplicate_document', 'shared_contact', 'ip_cluster', 'booking_velocity']);
            $t->json('conditions')->nullable();
            $t->enum('action', ['flag', 'review', 'block'])->default('flag');
            $t->unsignedSmallInteger('lookback_days')->default(90);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('fraud_hits', function (Blueprint $t) {
            $t->id();
            $t->foreignId('fraud_pattern_id')->constrained()->cascadeOnDelete();
            $t->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $t->nullableMorphs('subject');
            $t->unsignedTinyInteger('severity')->default(1);
            $t->json('details')->nullable();
            $t->enum('status', ['new', 'reviewed', 'dismissed'])->default('new');
            $t->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
        });

        Schema::create('insurance_claims', function (Blueprint $t) {
            $t->id();
            $t->string('claim_number', 40)->unique();
            $t->foreignId('insurance_policy_id')->constrained()->restrictOnDelete();
            $t->foreignId('damage_report_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('police_report_id')->nullable()->constrained()->nullOnDelete();
            $t->date('incident_date')->nullable();
            $t->decimal('filed_amount', 16, 2)->default(0);
            $t->decimal('approved_amount', 16, 2)->nullable();
            $t->enum('status', ['draft', 'submitted', 'in_review', 'approved', 'rejected', 'paid'])->default('draft');
            $t->json('documents')->nullable();
            $t->timestamp('submitted_at')->nullable();
            $t->timestamp('decided_at')->nullable();
            $t->text('notes')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('insurance_claims');
        Schema::dropIfExists('fraud_hits');
        Schema::dropIfExists('fraud_patterns');
        Schema::dropIfExists('face_verifications');
    }
};

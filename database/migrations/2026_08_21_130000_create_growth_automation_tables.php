<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->foreignId('location_id')->nullable()->after('id')->constrained('locations')->nullOnDelete();
        });
        Schema::create('rental_extensions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('rental_order_id')->constrained()->restrictOnDelete();
            $t->foreignId('customer_id')->constrained()->restrictOnDelete();
            $t->date('requested_end_date');
            $t->decimal('additional_amount', 14, 2)->default(0);
            $t->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
            $t->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('reviewed_at')->nullable();
            $t->text('reason')->nullable();
            $t->timestamps();
        });
        Schema::create('payment_transactions', function (Blueprint $t) {
            $t->id();
            $t->uuid('public_id')->unique();
            $t->foreignId('provider_id')->constrained()->restrictOnDelete();
            $t->foreignId('invoice_id')->nullable()->constrained()->restrictOnDelete();
            $t->foreignId('customer_id')->constrained()->restrictOnDelete();
            $t->string('external_id')->nullable()->index();
            $t->decimal('amount', 14, 2);
            $t->char('currency', 3)->default('IDR');
            $t->enum('type', ['charge', 'refund'])->default('charge');
            $t->enum('status', ['created', 'pending', 'paid', 'failed', 'expired', 'refunded'])->default('created');
            $t->text('checkout_url')->nullable();
            $t->json('request_payload')->nullable();
            $t->json('response_payload')->nullable();
            $t->string('callback_event_id')->nullable()->unique();
            $t->timestamp('paid_at')->nullable();
            $t->timestamps();
        });
        Schema::create('risk_rules', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('field');
            $t->enum('operator', ['equals', 'not_equals', 'gt', 'gte', 'lt', 'lte', 'contains', 'in']);
            $t->text('comparison_value')->nullable();
            $t->integer('score_delta')->default(0);
            $t->enum('action', ['allow', 'review', 'block'])->default('review');
            $t->unsignedSmallInteger('priority')->default(100);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });
        Schema::create('risk_assessments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $t->nullableMorphs('assessable');
            $t->string('fingerprint_hash')->nullable()->index();
            $t->integer('score')->default(0);
            $t->enum('decision', ['allow', 'review', 'block']);
            $t->json('matched_rules')->nullable();
            $t->json('context')->nullable();
            $t->timestamps();
        });
        Schema::create('vehicle_inspections', function (Blueprint $t) {
            $t->id();
            $t->foreignId('rental_order_id')->constrained()->restrictOnDelete();
            $t->foreignId('vehicle_id')->constrained()->restrictOnDelete();
            $t->foreignId('inspector_id')->nullable()->constrained('users')->nullOnDelete();
            $t->enum('type', ['checkout', 'checkin', 'maintenance']);
            $t->json('checklist');
            $t->json('photos')->nullable();
            $t->json('geo')->nullable();
            $t->text('customer_signature')->nullable();
            $t->text('staff_signature')->nullable();
            $t->enum('result', ['pass', 'attention', 'fail'])->default('pass');
            $t->timestamp('inspected_at');
            $t->timestamps();
        });
        Schema::create('gps_geofences', function (Blueprint $t) {
            $t->id();
            $t->foreignId('location_id')->nullable()->constrained()->nullOnDelete();
            $t->string('name');
            $t->enum('type', ['allowed', 'restricted', 'branch']);
            $t->json('geometry');
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });
        Schema::create('driver_behavior_events', function (Blueprint $t) {
            $t->id();
            $t->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $t->foreignId('driver_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('gps_log_id')->nullable()->constrained()->cascadeOnDelete();
            $t->enum('type', ['overspeed', 'harsh_brake', 'harsh_acceleration', 'long_idle', 'geofence_violation', 'towing', 'jammer']);
            $t->unsignedTinyInteger('severity')->default(1);
            $t->json('metrics')->nullable();
            $t->timestamp('occurred_at');
            $t->timestamps();
        });
        Schema::create('maintenance_predictions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $t->string('prediction_type');
            $t->date('predicted_date')->nullable();
            $t->unsignedInteger('predicted_km')->nullable();
            $t->decimal('confidence', 5, 2)->default(0);
            $t->json('factors')->nullable();
            $t->enum('status', ['open', 'scheduled', 'resolved', 'dismissed'])->default('open');
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_predictions');
        Schema::dropIfExists('driver_behavior_events');
        Schema::dropIfExists('gps_geofences');
        Schema::dropIfExists('vehicle_inspections');
        Schema::dropIfExists('risk_assessments');
        Schema::dropIfExists('risk_rules');
        Schema::dropIfExists('payment_transactions');
        Schema::dropIfExists('rental_extensions');
        Schema::table('users', fn (Blueprint $t) => $t->dropConstrainedForeignId('location_id'));
    }
};

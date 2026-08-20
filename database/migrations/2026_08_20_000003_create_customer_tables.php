<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->enum('customer_type', ['individual', 'corporate'])->default('individual');
            $t->string('email')->nullable();
            $t->string('phone')->index();
            $t->text('address')->nullable();
            $t->string('city')->nullable();
            $t->string('province')->nullable();
            $t->string('postal_code')->nullable();
            $t->string('ktp_number')->nullable();
            $t->string('sim_number')->nullable();
            $t->string('npwp')->nullable();
            $t->string('company_name')->nullable();
            $t->text('company_address')->nullable();
            $t->string('company_npwp')->nullable();
            $t->date('date_of_birth')->nullable();
            $t->enum('gender', ['male', 'female', 'other'])->nullable();
            $t->string('emergency_contact_name')->nullable();
            $t->string('emergency_contact_phone')->nullable();
            $t->unsignedTinyInteger('trust_score')->default(80);
            $t->decimal('total_spent', 14, 2)->default(0);
            $t->unsignedInteger('total_orders')->default(0);
            $t->enum('loyalty_tier', ['bronze', 'silver', 'gold', 'platinum'])->default('bronze');
            $t->enum('verification_status', ['incomplete', 'submitted', 'under_review', 'revision_required', 'verified', 'rejected', 'blocked'])->default('incomplete');
            $t->text('notes')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
            $t->softDeletes();

            $t->index('email');
            $t->index('customer_type');
            $t->index('verification_status');
        });

        Schema::create('customer_documents', function (Blueprint $t) {
            $t->id();
            $t->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $t->enum('document_type', ['ktp', 'sim', 'npwp', 'company_contract', 'selfie', 'other']);
            $t->string('document_number')->nullable();
            $t->string('document_url');
            $t->date('expiry_date')->nullable();
            $t->enum('status', ['pending', 'verified', 'rejected', 'expired'])->default('pending');
            $t->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('verified_at')->nullable();
            $t->text('rejection_reason')->nullable();
            $t->text('notes')->nullable();
            $t->timestamps();
        });

        Schema::create('bookings', function (Blueprint $t) {
            $t->id();
            $t->string('booking_number')->unique();
            $t->foreignId('customer_id')->constrained()->restrictOnDelete();
            $t->foreignId('vehicle_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('pickup_location_id')->nullable()->constrained('locations')->nullOnDelete();
            $t->foreignId('return_location_id')->nullable()->constrained('locations')->nullOnDelete();
            $t->foreignId('driver_id')->nullable()->constrained('drivers')->nullOnDelete();
            $t->date('start_date');
            $t->date('end_date');
            $t->date('estimated_return_date')->nullable();
            $t->enum('rental_type', ['self_drive', 'with_driver', 'airport_transfer', 'corporate'])->default('self_drive');
            $t->unsignedSmallInteger('duration_days')->nullable();
            $t->decimal('daily_rate_snapshot', 12, 2)->nullable();
            $t->decimal('subtotal', 14, 2)->default(0);
            $t->decimal('discount_amount', 14, 2)->default(0);
            $t->decimal('tax_amount', 14, 2)->default(0);
            $t->decimal('total_amount', 14, 2)->default(0);
            $t->decimal('deposit_amount', 14, 2)->default(0);
            $t->enum('status', ['inquiry', 'quoted', 'hold', 'pending_verification', 'pending_payment', 'confirmed', 'converted', 'cancelled', 'expired', 'no_show'])->default('inquiry');
            $t->text('cancellation_reason')->nullable();
            $t->timestamp('cancelled_at')->nullable();
            $t->timestamp('confirmed_at')->nullable();
            $t->timestamp('hold_expires_at')->nullable();
            $t->text('notes')->nullable();
            $t->string('source')->nullable();
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
            $t->softDeletes();

            $t->index('customer_id');
            $t->index('vehicle_id');
            $t->index('status');
            $t->index('start_date');
            $t->index('end_date');
            $t->index('hold_expires_at');
            $t->index('pickup_location_id');
        });

        Schema::create('quotations', function (Blueprint $t) {
            $t->id();
            $t->string('quotation_number')->unique();
            $t->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $t->string('customer_name')->nullable();
            $t->string('customer_phone')->nullable();
            $t->foreignId('vehicle_id')->nullable()->constrained()->nullOnDelete();
            $t->string('vehicle_name_snapshot')->nullable();
            $t->date('start_date');
            $t->date('end_date');
            $t->enum('rental_type', ['self_drive', 'with_driver', 'airport_transfer', 'corporate'])->default('self_drive');
            $t->unsignedSmallInteger('duration_days');
            $t->decimal('daily_rate', 12, 2);
            $t->decimal('subtotal', 14, 2)->default(0);
            $t->decimal('addon_total', 14, 2)->default(0);
            $t->decimal('discount_amount', 14, 2)->default(0);
            $t->decimal('tax_amount', 14, 2)->default(0);
            $t->decimal('total_amount', 14, 2)->default(0);
            $t->decimal('deposit_amount', 14, 2)->default(0);
            $t->enum('status', ['draft', 'sent', 'viewed', 'accepted', 'rejected', 'expired', 'converted'])->default('draft');
            $t->date('valid_until')->nullable();
            $t->text('notes')->nullable();
            $t->text('terms_conditions')->nullable();
            $t->text('lost_reason')->nullable();
            $t->foreignId('converted_to_booking_id')->nullable()->constrained('bookings')->nullOnDelete();
            $t->timestamp('sent_at')->nullable();
            $t->timestamp('viewed_at')->nullable();
            $t->timestamp('accepted_at')->nullable();
            $t->timestamp('rejected_at')->nullable();
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
            $t->softDeletes();
        });

        Schema::create('quotation_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('quotation_id')->constrained()->cascadeOnDelete();
            $t->foreignId('vehicle_id')->constrained()->restrictOnDelete();
            $t->decimal('daily_rate', 12, 2);
            $t->decimal('total', 14, 2);
            $t->text('notes')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotation_items');
        Schema::dropIfExists('quotations');
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('customer_documents');
        Schema::dropIfExists('customers');
    }
};

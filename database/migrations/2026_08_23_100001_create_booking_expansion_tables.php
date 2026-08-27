<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('group_bookings', function (Blueprint $t) {
            $t->id();
            $t->string('code', 20)->unique();
            $t->string('event_name');
            $t->string('contact_name');
            $t->string('contact_phone', 30);
            $t->string('contact_email')->nullable();
            $t->unsignedSmallInteger('units_needed')->default(1);
            $t->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('location_id')->nullable()->constrained()->nullOnDelete();
            $t->date('start_date')->nullable();
            $t->date('end_date')->nullable();
            $t->decimal('quoted_total', 14, 2)->default(0);
            $t->enum('status', ['inquiry', 'quoted', 'confirmed', 'completed', 'cancelled'])->default('inquiry');
            $t->text('notes')->nullable();
            $t->timestamps();
        });

        Schema::table('bookings', function (Blueprint $t) {
            $t->foreignId('group_booking_id')->nullable()->constrained('group_bookings')->nullOnDelete();
            $t->string('pickup_city')->nullable();
            $t->string('return_city')->nullable();
            $t->decimal('relocation_fee', 14, 2)->default(0);
            $t->string('session_id')->nullable()->index();
        });

        Schema::create('booking_holds', function (Blueprint $t) {
            $t->id();
            $t->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $t->uuid('hold_token')->unique();
            $t->string('session_id')->nullable()->index();
            $t->date('start_date');
            $t->date('end_date');
            $t->timestamp('expires_at')->index();
            $t->enum('status', ['active', 'converted', 'expired', 'released'])->default('active');
            $t->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
            $t->timestamps();
        });

        Schema::create('abandoned_bookings', function (Blueprint $t) {
            $t->id();
            $t->string('session_id')->index();
            $t->string('name')->nullable();
            $t->string('email')->nullable();
            $t->string('phone', 30)->nullable();
            $t->foreignId('vehicle_id')->nullable()->constrained()->nullOnDelete();
            $t->json('quote_snapshot')->nullable();
            $t->string('last_step', 50)->default('quote');
            $t->unsignedTinyInteger('reminders_sent')->default(0);
            $t->enum('status', ['open', 'recovered', 'expired', 'opted_out'])->default('open');
            $t->foreignId('recovered_booking_id')->nullable()->constrained('bookings')->nullOnDelete();
            $t->timestamp('last_activity_at')->index();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('abandoned_bookings');
        Schema::dropIfExists('booking_holds');
        Schema::table('bookings', function (Blueprint $t) {
            $t->dropConstrainedForeignId('group_booking_id');
            $t->dropColumn(['pickup_city', 'return_city', 'relocation_fee', 'session_id']);
        });
        Schema::dropIfExists('group_bookings');
    }
};

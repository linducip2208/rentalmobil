<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void { Schema::create('booking_waitlists', function (Blueprint $table) {
        $table->id(); $table->foreignId('customer_id')->constrained('customers')->restrictOnDelete();
        $table->foreignId('category_id')->nullable()->constrained('categories')->restrictOnDelete();
        $table->foreignId('location_id')->nullable()->constrained('locations')->restrictOnDelete();
        $table->date('start_date'); $table->date('end_date'); $table->unsignedSmallInteger('priority')->default(100);
        $table->enum('status', ['waiting', 'offered', 'converted', 'expired', 'cancelled'])->default('waiting');
        $table->timestamp('offered_at')->nullable(); $table->timestamp('expires_at')->nullable();
        $table->foreignId('converted_booking_id')->nullable()->constrained('bookings')->nullOnDelete();
        $table->text('notes')->nullable(); $table->timestamps();
        $table->index(['status', 'start_date', 'end_date', 'priority'], 'waitlist_matching_index');
    }); }
    public function down(): void { Schema::dropIfExists('booking_waitlists'); }
};

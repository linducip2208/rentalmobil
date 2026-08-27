<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('customer_id')->constrained()->restrictOnDelete();
            $t->foreignId('vehicle_id')->constrained()->restrictOnDelete();
            $t->string('plan_name');
            $t->enum('billing_cycle', ['monthly', 'quarterly', 'yearly'])->default('monthly');
            $t->decimal('price_per_cycle', 14, 2);
            $t->date('start_date');
            $t->date('current_period_end')->nullable();
            $t->boolean('auto_renew')->default(true);
            $t->unsignedInteger('included_km_per_cycle')->nullable();
            $t->decimal('overage_km_rate', 12, 2)->nullable();
            $t->enum('status', ['active', 'paused', 'cancelled', 'expired'])->default('active');
            $t->date('cancelled_at')->nullable();
            $t->text('notes')->nullable();
            $t->timestamps();

            $t->index(['status', 'current_period_end']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};

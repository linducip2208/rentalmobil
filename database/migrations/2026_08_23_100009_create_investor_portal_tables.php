<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('investor_accounts', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('email')->nullable();
            $t->string('phone', 30)->nullable();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->boolean('is_active')->default(true);
            $t->text('notes')->nullable();
            $t->timestamps();
        });

        Schema::create('vehicle_investments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('investor_account_id')->constrained('investor_accounts')->cascadeOnDelete();
            $t->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $t->decimal('share_percent', 5, 2)->default(100);
            $t->decimal('invested_amount', 16, 2)->default(0);
            $t->date('started_at');
            $t->date('ended_at')->nullable();
            $t->enum('status', ['active', 'ended'])->default('active');
            $t->timestamps();
        });

        Schema::create('investor_distributions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('vehicle_investment_id')->constrained('vehicle_investments')->cascadeOnDelete();
            $t->char('period_month', 7);
            $t->decimal('revenue_share', 16, 2)->default(0);
            $t->decimal('expense_share', 16, 2)->default(0);
            $t->decimal('depreciation_share', 16, 2)->default(0);
            $t->decimal('net_payout', 16, 2)->default(0);
            $t->enum('status', ['pending', 'paid'])->default('pending');
            $t->timestamp('paid_at')->nullable();
            $t->timestamps();
            $t->unique(['vehicle_investment_id', 'period_month'], 'investor_distributions_unique_period');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('investor_distributions');
        Schema::dropIfExists('vehicle_investments');
        Schema::dropIfExists('investor_accounts');
    }
};

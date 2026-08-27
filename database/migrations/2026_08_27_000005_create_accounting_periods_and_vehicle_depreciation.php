<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounting_periods', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('fiscal_year');
            $table->unsignedTinyInteger('period_number');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status', 20)->default('open')->index();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('closed_at')->nullable();
            $table->text('closing_notes')->nullable();
            $table->timestamps();
            $table->unique(['fiscal_year', 'period_number']);
            $table->index(['start_date', 'end_date']);
        });

        Schema::table('vehicles', function (Blueprint $table) {
            $table->decimal('residual_value', 16, 2)->default(0)->after('purchase_price');
            $table->string('depreciation_method', 30)->default('straight_line')->after('useful_life_months');
            $table->date('depreciation_start_date')->nullable()->after('acquired_at');
            $table->decimal('accumulated_depreciation', 16, 2)->default(0)->after('depreciation_start_date');
            $table->foreignId('asset_account_id')->nullable()->constrained('chart_of_accounts')->restrictOnDelete();
            $table->foreignId('accumulated_depreciation_account_id')->nullable()->constrained('chart_of_accounts')->restrictOnDelete();
            $table->foreignId('depreciation_expense_account_id')->nullable()->constrained('chart_of_accounts')->restrictOnDelete();
        });

        Schema::create('vehicle_depreciation_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->restrictOnDelete();
            $table->foreignId('accounting_period_id')->constrained('accounting_periods')->restrictOnDelete();
            $table->foreignId('journal_entry_id')->constrained('journal_entries')->restrictOnDelete();
            $table->decimal('amount', 16, 2);
            $table->date('posting_date');
            $table->timestamps();
            $table->unique(['vehicle_id', 'accounting_period_id'], 'vehicle_period_depreciation_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_depreciation_runs');
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropForeign(['asset_account_id']);
            $table->dropForeign(['accumulated_depreciation_account_id']);
            $table->dropForeign(['depreciation_expense_account_id']);
            $table->dropColumn(['residual_value', 'depreciation_method', 'depreciation_start_date', 'accumulated_depreciation', 'asset_account_id', 'accumulated_depreciation_account_id', 'depreciation_expense_account_id']);
        });
        Schema::dropIfExists('accounting_periods');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('deposits', function (Blueprint $t) {
            $t->timestamp('auto_refund_scheduled_at')->nullable();
            $t->string('refund_channel', 50)->nullable();
        });

        Schema::create('recon_matching_rules', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('match_field', 30)->default('description');
            $t->enum('operator', ['contains', 'equals', 'starts_with', 'regex', 'amount_within']);
            $t->text('value')->nullable();
            $t->unsignedSmallInteger('priority')->default(100);
            $t->boolean('auto_match')->default(false);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('cash_flow_snapshots', function (Blueprint $t) {
            $t->id();
            $t->date('as_of_date')->index();
            $t->unsignedSmallInteger('horizon_days')->default(90);
            $t->decimal('projected_inflow', 16, 2)->default(0);
            $t->decimal('projected_outflow', 16, 2)->default(0);
            $t->decimal('net_projection', 16, 2)->default(0);
            $t->json('breakdown')->nullable();
            $t->timestamps();
        });

        Schema::create('exchange_rates', function (Blueprint $t) {
            $t->id();
            $t->char('currency_code', 3)->index();
            $t->decimal('rate_to_idr', 16, 4);
            $t->date('effective_date');
            $t->string('source', 100)->nullable();
            $t->timestamps();
        });

        Schema::table('invoices', fn (Blueprint $t) => $t->char('currency', 3)->default('IDR'));

        Schema::create('einvoice_submissions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $t->foreignId('provider_id')->nullable()->constrained()->nullOnDelete();
            $t->string('submission_ref')->nullable()->index();
            $t->enum('status', ['draft', 'submitted', 'accepted', 'rejected'])->default('draft');
            $t->json('payload')->nullable();
            $t->json('response')->nullable();
            $t->timestamp('submitted_at')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('einvoice_submissions');
        Schema::table('invoices', fn (Blueprint $t) => $t->dropColumn('currency'));
        Schema::dropIfExists('exchange_rates');
        Schema::dropIfExists('cash_flow_snapshots');
        Schema::dropIfExists('recon_matching_rules');
        Schema::table('deposits', fn (Blueprint $t) => $t->dropColumn(['auto_refund_scheduled_at', 'refund_channel']));
    }
};

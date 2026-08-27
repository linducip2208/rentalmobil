<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_statement_imports', function (Blueprint $t) {
            $t->id();
            $t->foreignId('bank_account_id')->nullable()->constrained()->nullOnDelete();
            $t->string('file_name');
            $t->date('period_start')->nullable();
            $t->date('period_end')->nullable();
            $t->unsignedInteger('total_lines')->default(0);
            $t->unsignedInteger('matched_count')->default(0);
            $t->enum('status', ['processing', 'ready', 'posted'])->default('processing');
            $t->foreignId('imported_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
        });

        Schema::create('bank_statement_lines', function (Blueprint $t) {
            $t->id();
            $t->foreignId('import_id')->constrained('bank_statement_imports')->cascadeOnDelete();
            $t->date('transaction_date');
            $t->string('description');
            $t->decimal('amount_in', 14, 2)->default(0);
            $t->decimal('amount_out', 14, 2)->default(0);
            $t->string('reference')->nullable();
            $t->enum('match_status', ['unmatched', 'matched', 'ignored', 'conflict'])->default('unmatched');
            $t->foreignId('matched_payment_id')->nullable()->constrained('payments')->nullOnDelete();
            $t->decimal('match_confidence', 5, 2)->nullable();
            $t->text('match_note')->nullable();
            $t->timestamps();

            $t->index(['import_id', 'match_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_statement_lines');
        Schema::dropIfExists('bank_statement_imports');
    }
};

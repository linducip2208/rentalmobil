<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $t) {
            $t->date('stnk_due_date')->nullable()->comment('Jatuh tempo perpanjangan STNK / mutasi');
            $t->date('tax_due_date')->nullable()->comment('Pajak tahunan');
            $t->date('tax_5y_due_date')->nullable()->comment('Pajak 5 tahunan');
            $t->date('kir_due_date')->nullable()->comment('Uji berkala KIR');
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $t) {
            $t->dropColumn(['stnk_due_date', 'tax_due_date', 'tax_5y_due_date', 'kir_due_date']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $t) {
            $t->date('ktp_expiry_date')->nullable()->comment('Masa berlaku KTP (lifetime kecuali e-KTP sementara)');
            $t->date('sim_expiry_date')->nullable()->comment('Masa berlaku SIM — booking diblok jika kedaluwarsa selama sewa');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $t) {
            $t->dropColumn(['ktp_expiry_date', 'sim_expiry_date']);
        });
    }
};

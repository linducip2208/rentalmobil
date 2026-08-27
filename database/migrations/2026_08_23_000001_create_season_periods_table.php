<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('season_periods', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->date('start_date');
            $t->date('end_date');
            $t->decimal('multiplier', 5, 2)->default(1.0);
            $t->boolean('is_recurring_annual')->default(false)->comment('Ulang tiap tahun (libur nasional, high season)');
            $t->foreignId('location_id')->nullable()->constrained()->nullOnDelete()->comment('Kosong = berlaku semua cabang');
            $t->boolean('is_active')->default(true);
            $t->timestamps();

            $t->index(['start_date', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('season_periods');
    }
};

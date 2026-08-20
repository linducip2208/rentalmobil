<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE approval_workflows MODIFY type ENUM('discount','refund','deposit_deduction','expense','maintenance','purchase_order','booking','payment','rental_order') NOT NULL");
            DB::statement("ALTER TABLE approval_workflows MODIFY status ENUM('pending','approved','rejected','escalated') NOT NULL DEFAULT 'pending'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("DELETE FROM approval_workflows WHERE type IN ('booking','payment','rental_order')");
            DB::statement("UPDATE approval_workflows SET status = 'pending' WHERE status = 'escalated'");
            DB::statement("ALTER TABLE approval_workflows MODIFY status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending'");
            DB::statement("ALTER TABLE approval_workflows MODIFY type ENUM('discount','refund','deposit_deduction','expense','maintenance','purchase_order') NOT NULL");
        }
    }
};

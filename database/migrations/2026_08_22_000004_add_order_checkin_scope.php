<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("ALTER TABLE secure_tokens MODIFY scope ENUM('quotation_view','quotation_approve','document_upload','contract_sign','invoice_view','receipt_download','deposit_approval','survey','order_checkin') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE secure_tokens MODIFY scope ENUM('quotation_view','quotation_approve','document_upload','contract_sign','invoice_view','receipt_download','deposit_approval','survey') NOT NULL");
    }
};

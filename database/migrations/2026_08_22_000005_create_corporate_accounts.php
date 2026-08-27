<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('corporate_accounts', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('contact_name')->nullable();
            $t->string('contact_email')->nullable();
            $t->string('contact_phone')->nullable();
            $t->string('tax_id')->nullable()->comment('NPWP perusahaan');
            $t->text('address')->nullable();
            $t->decimal('credit_limit', 16, 2)->default(0);
            $t->unsignedSmallInteger('payment_terms_days')->default(30);
            $t->decimal('discount_percent', 5, 2)->default(0);
            $t->boolean('is_active')->default(true);
            $t->text('notes')->nullable();
            $t->timestamps();
        });

        Schema::table('customers', function (Blueprint $t) {
            $t->foreignId('corporate_account_id')->nullable()->after('customer_type')->constrained('corporate_accounts')->nullOnDelete();
        });

        foreach (['bookings', 'rental_orders'] as $table) {
            Schema::table($table, function (Blueprint $t) use ($table) {
                if (Schema::hasColumn($table, 'purchase_order_number')) {
                    return;
                }
                $t->string('purchase_order_number')->nullable();
            });
        }
    }

    public function down(): void
    {
        foreach (['rental_orders', 'bookings'] as $table) {
            Schema::table($table, fn (Blueprint $t) => $t->dropColumnIfExists('purchase_order_number'));
        }
        Schema::table('customers', fn (Blueprint $t) => $t->dropConstrainedForeignId('corporate_account_id'));
        Schema::dropIfExists('corporate_accounts');
    }
};

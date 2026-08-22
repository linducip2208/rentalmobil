<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUpTraits()
    {
        if (app()->configurationIsCached()) {
            throw new \RuntimeException('TEST DIHENTIKAN: config cache terdeteksi (bootstrap/cache/config.php). Cache mem-override APP_ENV & DB_DATABASE dari phpunit.xml sehingga test bisa jalan di database development asli. Jalankan: php artisan optimize:clear');
        }

        $database = (string) config('database.connections.mysql.database');
        if (!str_ends_with($database, '_testing')) {
            throw new \RuntimeException("TEST DIHENTIKAN: database aktif '{$database}' bukan database *_testing. Jalankan php artisan optimize:clear sebelum test.");
        }

        return parent::setUpTraits();
    }

    protected function setUp(): void
    {
        parent::setUp();

        if (!\Illuminate\Support\Facades\Schema::hasTable('locations')) {
            return;
        }

        $now = now();
        \Illuminate\Support\Facades\DB::table('locations')->insertOrIgnore(['id' => 1, 'name' => 'Cabang Test', 'slug' => 'cabang-test', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]);
        \Illuminate\Support\Facades\DB::table('categories')->insertOrIgnore(['id' => 1, 'name' => 'MPV Test', 'slug' => 'mpv-test', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]);
        \Illuminate\Support\Facades\DB::table('brands')->insertOrIgnore(['id' => 1, 'name' => 'Brand Test', 'slug' => 'brand-test', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]);
        \Illuminate\Support\Facades\DB::table('brands')->insertOrIgnore(['id' => 2, 'name' => 'Brand Test 2', 'slug' => 'brand-test-2', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]);
        \Illuminate\Support\Facades\DB::table('users')->insertOrIgnore(['id' => 1, 'name' => 'System Test', 'email' => 'system@test.local', 'password' => bcrypt('password'), 'role' => 'admin', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]);

        if (\Illuminate\Support\Facades\Schema::hasTable('chart_of_accounts')) {
            $coa = [
                ['code' => '1101', 'name' => 'Kas & Bank', 'type' => 'asset', 'normal_balance' => 'debit'],
                ['code' => '1102', 'name' => 'Piutang Usaha', 'type' => 'asset', 'normal_balance' => 'debit'],
                ['code' => '2101', 'name' => 'Uang Muka Pelanggan', 'type' => 'liability', 'normal_balance' => 'credit'],
                ['code' => '4101', 'name' => 'Pendapatan Sewa', 'type' => 'revenue', 'normal_balance' => 'credit'],
                ['code' => '4102', 'name' => 'Pendapatan Denda Keterlambatan', 'type' => 'revenue', 'normal_balance' => 'credit'],
                ['code' => '4103', 'name' => 'Pendapatan Klaim Kerusakan', 'type' => 'revenue', 'normal_balance' => 'credit'],
            ];
            foreach ($coa as $account) {
                \Illuminate\Support\Facades\DB::table('chart_of_accounts')->insertOrIgnore(array_merge($account, ['is_active' => true, 'is_system' => true, 'created_at' => $now, 'updated_at' => $now]));
            }
        }
    }
}

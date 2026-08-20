<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUpTraits()
    {
        $database = (string) config('database.connections.mysql.database');
        if (app()->environment('testing') && !str_ends_with($database, '_testing')) {
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
    }
}

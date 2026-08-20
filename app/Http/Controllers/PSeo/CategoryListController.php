<?php

namespace App\Http\Controllers\PSeo;

class CategoryListController extends BasePseoController
{
    public function index()
    {
        $categories = [
            ['name' => 'SUV', 'slug' => 'suv', 'icon' => '🏔️', 'description' => 'Kendaraan tangguh untuk medan off-road dan perjalanan jauh'],
            ['name' => 'Sedan', 'slug' => 'sedan', 'icon' => '🏎️', 'description' => 'Kendaraan nyaman dan irit untuk perjalanan bisnis'],
            ['name' => 'MPV', 'slug' => 'mpv', 'icon' => '👨‍👩‍👧‍👦', 'description' => 'Kendaraan luas untuk keluarga dan rombongan'],
            ['name' => 'City Car', 'slug' => 'city-car', 'icon' => '🚗', 'description' => 'Kendaraan kompak untuk mobilitas perkotaan'],
            ['name' => 'Pickup', 'slug' => 'pickup', 'icon' => '📦', 'description' => 'Kendaraan niaga untuk angkutan barang'],
            ['name' => 'Bus Pariwisata', 'slug' => 'bus', 'icon' => '🚌', 'description' => 'Bus besar untuk rombongan wisata dan event'],
        ];

        $jsonLd = $this->jsonLdItemList(
            array_map(fn($c) => ['name' => $c['name'], 'slug' => $c['slug']], $categories),
            'Kategori Sewa Mobil Indonesia',
            url('/sewa-mobil')
        );

        return view('pseo.category-list', array_merge(
            $this->seoMeta(
                'Sewa Mobil Indonesia — Kategori Lengkap | RentalMobil',
                'Jelajahi kategori sewa mobil: SUV, Sedan, MPV, City Car, Pickup, dan Bus Pariwisata. Armada lengkap di seluruh Indonesia.',
                url('/sewa-mobil'),
                $jsonLd
            ),
            compact('categories')
        ));
    }
}

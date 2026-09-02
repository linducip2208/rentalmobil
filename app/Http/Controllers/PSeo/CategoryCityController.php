<?php

namespace App\Http\Controllers\PSeo;

use App\Models\Category;
use App\Models\Location;
use App\Models\Vehicle;

/**
 * Programmatic SEO landing page per city (/sewa-mobil-di-{city}).
 *
 * All pricing and fleet data comes from the database — per-category prices
 * are computed from the real fleet, never hardcoded.
 */
class CategoryCityController extends BasePseoController
{
    protected array $cityDescriptions = [
        'Jakarta' => 'Jakarta sebagai ibu kota Indonesia memiliki permintaan rental mobil tertinggi. Dari kebutuhan antar-jemput bandara, perjalanan bisnis di CBD, hingga weekend getaway ke Puncak atau Bandung. Armada kami di Jakarta tersedia lengkap dari city car hingga bus pariwisata.',
        'Bandung' => 'Bandung menjadi destinasi populer untuk wisata kuliner dan belanja. Menyewa mobil di Bandung memudahkan Anda menjelajahi Lembang, Ciwidey, hingga Tangkuban Perahu tanpa batasan jadwal transportasi umum.',
        'Surabaya' => 'Surabaya sebagai kota terbesar kedua di Indonesia memiliki kebutuhan rental mobil yang tinggi untuk bisnis dan wisata. Akses mudah ke Malang, Bromo, dan Banyuwangi menjadikan sewa mobil pilihan terbaik.',
    ];

    public function __invoke(string $city)
    {
        $cityTitle = ucwords(str_replace('-', ' ', $city));

        $location = Location::query()
            ->active()
            ->where('city', $cityTitle)
            ->first();

        $vehicles = Vehicle::query()
            ->with(['category', 'brand', 'location', 'photos'])
            ->where('is_active', true)
            ->when($location, fn ($q) => $q->where('location_id', $location->id))
            ->orderBy('daily_rate')
            ->limit(12)
            ->get();

        $priceRows = $this->priceRows($location?->id);

        $description = $this->cityDescriptions[$cityTitle]
            ?? "Menyewa mobil di {$cityTitle} memberikan Anda kebebasan menjelajahi kota dan sekitarnya. Kami menyediakan armada terawat dengan harga transparan dan proses serah terima digital.";

        $jsonLd = $this->jsonLdLocalBusiness($cityTitle);

        return view('pseo.category-city', array_merge(
            $this->seoMeta(
                "Sewa Mobil di {$cityTitle} — Harga Terbaik | RentalMobil",
                "Sewa mobil murah dan terpercaya di {$cityTitle}. Katalog lengkap, harga transparan, booking online 24/7.",
                url("/sewa-mobil-di-{$city}"),
                $jsonLd
            ),
            [
                'city' => $cityTitle,
                'citySlug' => $city,
                'cityDescription' => $description,
                'location' => $location,
                'vehicles' => $vehicles,
                'priceRows' => $priceRows,
            ]
        ));
    }

    /**
     * Real per-category price bands from the database (city-scoped when the
     * branch exists, otherwise fleet-wide).
     */
    private function priceRows(?int $locationId): array
    {
        $categories = Category::query()
            ->where('is_active', true)
            ->with(['vehicles' => fn ($q) => $q
                ->where('is_active', true)
                ->when($locationId, fn ($v) => $v->where('location_id', $locationId))])
            ->orderBy('sort_order')
            ->get();

        $rows = [];

        foreach ($categories as $category) {
            if ($category->vehicles->isEmpty()) {
                continue;
            }

            $rows[] = [
                'name' => $category->name,
                'min_rate' => $category->vehicles->min('daily_rate'),
                'units' => $category->vehicles->count(),
            ];
        }

        return $rows;
    }
}

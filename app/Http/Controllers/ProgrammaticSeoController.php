<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class ProgrammaticSeoController extends Controller
{
    public function bestCategory(string $category, ?int $year = null)
    {
        $categoryModel = Category::where('slug', $category)
            ->orWhereRaw("LOWER(name) = ?", strtolower(str_replace('-', ' ', $category)))
            ->firstOrFail();

        $vehicles = Vehicle::where('category_id', $categoryModel->id)
            ->where('is_active', true)
            ->when($year, fn ($q) => $q->where('year', $year))
            ->with(['category', 'brand', 'location'])
            ->get();

        $title = "Best {$categoryModel->name}" . ($year ? " {$year}" : "") . " — Top Pilihan Sewa | RentalMobil";
        $description = "Daftar terbaik {$categoryModel->name}" . ($year ? " tahun {$year}" : "") . " untuk disewa di RentalMobil. Spesifikasi, harga, dan ulasan lengkap.";

        $jsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'name' => $title,
            'url' => url()->current(),
            'itemListElement' => $vehicles->map(fn ($v, $i) => [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'name' => $v->name,
                'url' => url('/sewa/' . $v->slug),
                'description' => "{$v->brand?->name} {$v->name} {$v->year} — Rp " . number_format((float) $v->daily_rate, 0, ',', '.') . "/hari",
            ])->toArray(),
        ];

        return view('pseo.best-category', [
            'seoTitle' => $title,
            'seoDescription' => $description,
            'seoCanonical' => url()->current(),
            'seoJsonLd' => json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            'category' => $categoryModel,
            'vehicles' => $vehicles,
            'year' => $year,
        ]);
    }

    public function alternativesTo(string $slug)
    {
        $vehicleName = ucwords(str_replace('-', ' ', $slug));

        $baseVehicle = Vehicle::where('slug', $slug)->first();
        $alternatives = collect();

        if ($baseVehicle) {
            $alternatives = Vehicle::where('category_id', $baseVehicle->category_id)
                ->where('id', '!=', $baseVehicle->id)
                ->where('is_active', true)
                ->with(['category', 'brand', 'location'])
                ->limit(10)
                ->get();
        }

        $title = "Alternatif {$vehicleName} — Kendaraan Serupa untuk Disewa | RentalMobil";
        $description = "Mencari alternatif {$vehicleName}? Lihat kendaraan serupa dari kategori yang sama dengan harga terbaik di RentalMobil.";

        $jsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'name' => $title,
            'url' => url()->current(),
            'itemListElement' => $alternatives->map(fn ($v, $i) => [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'name' => $v->name,
                'url' => url('/sewa/' . $v->slug),
            ])->toArray(),
        ];

        return view('pseo.alternatives', [
            'seoTitle' => $title,
            'seoDescription' => $description,
            'seoCanonical' => url()->current(),
            'seoJsonLd' => json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            'vehicleName' => $vehicleName,
            'slug' => $slug,
            'baseVehicle' => $baseVehicle,
            'alternatives' => $alternatives,
        ]);
    }

    public function compareVehicles(string $a, string $b)
    {
        $vehicleA = Vehicle::where('slug', $a)->first();
        $vehicleB = Vehicle::where('slug', $b)->first();

        $nameA = $vehicleA ? $vehicleA->name : ucwords(str_replace('-', ' ', $a));
        $nameB = $vehicleB ? $vehicleB->name : ucwords(str_replace('-', ' ', $b));

        $title = "{$nameA} vs {$nameB} — Perbandingan Lengkap | RentalMobil";
        $description = "Perbandingan lengkap {$nameA} vs {$nameB}: spesifikasi, harga sewa, kapasitas, dan fitur. Pilih kendaraan terbaik untuk kebutuhan Anda.";

        $jsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'name' => $title,
            'url' => url()->current(),
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => $nameA, 'url' => url('/sewa/' . $a)],
                ['@type' => 'ListItem', 'position' => 2, 'name' => $nameB, 'url' => url('/sewa/' . $b)],
            ],
        ];

        return view('pseo.compare', [
            'seoTitle' => $title,
            'seoDescription' => $description,
            'seoCanonical' => url()->current(),
            'seoJsonLd' => json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            'vehicleA' => $nameA,
            'vehicleB' => $nameB,
            'slugA' => $a,
            'slugB' => $b,
            'specA' => $vehicleA ? $this->vehicleSpecs($vehicleA) : [],
            'specB' => $vehicleB ? $this->vehicleSpecs($vehicleB) : [],
            'verdict' => $this->generateVerdict($vehicleA, $vehicleB),
        ]);
    }

    public function sourceCode()
    {
        $title = "Beli Aplikasi Rental Mobil — Source Code Laravel | RentalMobil";
        $description = "Beli source code aplikasi rental mobil berbasis Laravel. Fitur lengkap: booking, invoice, GPS tracking, multi-lokasi. Siap deploy.";

        $jsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => 'Aplikasi Rental Mobil — Source Code Laravel',
            'description' => $description,
            'brand' => ['@type' => 'Brand', 'name' => 'RentalMobil'],
            'offers' => [
                '@type' => 'Offer',
                'price' => '0',
                'priceCurrency' => 'IDR',
                'availability' => 'https://schema.org/InStock',
            ],
        ];

        return view('pseo.source-code', [
            'seoTitle' => $title,
            'seoDescription' => $description,
            'seoCanonical' => url()->current(),
            'seoJsonLd' => json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        ]);
    }

    protected function vehicleSpecs(Vehicle $vehicle): array
    {
        return [
            'Merk' => $vehicle->brand?->name ?? '-',
            'Tahun' => $vehicle->year,
            'Kapasitas' => $vehicle->seat_count . ' kursi',
            'Transmisi' => $vehicle->transmission,
            'Bahan Bakar' => $vehicle->fuel_type,
            'Warna' => $vehicle->color,
            'Kapasitas Mesin' => $vehicle->engine_cc . ' cc',
            'Harga/Hari' => 'Rp ' . number_format((float) $vehicle->daily_rate, 0, ',', '.'),
            'Deposit' => 'Rp ' . number_format((float) $vehicle->deposit_amount, 0, ',', '.'),
        ];
    }

    protected function generateVerdict(?Vehicle $a, ?Vehicle $b): ?string
    {
        if (!$a || !$b) {
            return null;
        }

        $rateA = (float) $a->daily_rate;
        $rateB = (float) $b->daily_rate;

        if ($rateA < $rateB) {
            return "{$a->name} lebih terjangkau, sedangkan {$b->name} menawarkan fitur lebih lengkap.";
        } elseif ($rateB < $rateA) {
            return "{$b->name} lebih terjangkau, sedangkan {$a->name} menawarkan fitur lebih lengkap.";
        }

        return "Kedua kendaraan memiliki harga yang serupa. Pilihan tergantung kebutuhan spesifik Anda.";
    }
}

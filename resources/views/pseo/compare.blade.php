@extends('pseo._layout')

@section('content')
<section class="py-16 lg:py-24">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="font-bold text-4xl lg:text-5xl text-stone-900 mb-4">{{ $vehicleA ?? 'Kendaraan A' }} vs {{ $vehicleB ?? 'Kendaraan B' }}</h1>
        <p class="text-lg text-stone-500 mb-12">Perbandingan lengkap untuk membantu Anda memilih kendaraan yang tepat</p>

        {{-- Comparison Table --}}
        <div class="bg-white border border-stone-200 rounded-2xl overflow-hidden mb-12 shadow-sm">
            <div class="grid grid-cols-3 bg-stone-900 text-white">
                <div class="p-4 font-semibold text-sm">Spesifikasi</div>
                <div class="p-4 font-semibold text-sm text-center">{{ $vehicleA ?? 'Kendaraan A' }}</div>
                <div class="p-4 font-semibold text-sm text-center">{{ $vehicleB ?? 'Kendaraan B' }}</div>
            </div>
            @foreach([
                ['label' => 'Tipe', 'a' => $specA['type'] ?? 'SUV', 'b' => $specB['type'] ?? 'Sedan'],
                ['label' => 'Tahun', 'a' => $specA['year'] ?? '2024', 'b' => $specB['year'] ?? '2023'],
                ['label' => 'Mesin', 'a' => $specA['engine'] ?? '1.5L Turbo', 'b' => $specB['engine'] ?? '2.0L NA'],
                ['label' => 'Transmisi', 'a' => $specA['transmission'] ?? 'Automatic', 'b' => $specB['transmission'] ?? 'Automatic'],
                ['label' => 'BBM', 'a' => $specA['fuel'] ?? 'Bensin', 'b' => $specB['fuel'] ?? 'Diesel'],
                ['label' => 'Kursi', 'a' => $specA['seats'] ?? '7', 'b' => $specB['seats'] ?? '5'],
                ['label' => 'Kapasitas Bagasi', 'a' => $specA['luggage'] ?? '500L', 'b' => $specB['luggage'] ?? '450L'],
                ['label' => 'Harga/Hari', 'a' => $specA['price'] ?? 'Rp 500.000', 'b' => $specB['price'] ?? 'Rp 350.000'],
                ['label' => 'Konsumsi BBM', 'a' => $specA['consumption'] ?? '12 km/L', 'b' => $specB['consumption'] ?? '15 km/L'],
                ['label' => 'Rating', 'a' => $specA['rating'] ?? '⭐ 4.8', 'b' => $specB['rating'] ?? '⭐ 4.6'],
            ] as $row)
            <div class="grid grid-cols-3 border-t border-stone-100 {{ $loop->even ? 'bg-stone-50' : '' }}">
                <div class="p-4 text-sm font-medium text-stone-600">{{ $row['label'] }}</div>
                <div class="p-4 text-sm text-center text-stone-800 font-medium">{{ $row['a'] }}</div>
                <div class="p-4 text-sm text-center text-stone-800 font-medium">{{ $row['b'] }}</div>
            </div>
            @endforeach
        </div>

        {{-- Verdict --}}
        <div class="bg-brand-50 border border-brand-200 rounded-2xl p-8 mb-12">
            <h2 class="font-bold text-xl text-stone-900 mb-3">💡 Kesimpulan</h2>
            <div class="prose prose-stone prose-sm max-w-none">
                <p>{{ $verdict ?? "Pilihan terbaik tergantung pada kebutuhan Anda. Jika Anda membutuhkan kendaraan untuk perjalanan keluarga dengan banyak penumpang dan bagasi, pilih kendaraan yang lebih besar. Namun jika Anda lebih mementingkan efisiensi bahan bakar dan kemudahan bermanuver di perkotaan, kendaraan yang lebih kompak bisa menjadi pilihan yang lebih tepat." }}</p>
            </div>
        </div>

        {{-- CTA --}}
        <div class="grid sm:grid-cols-2 gap-6">
            <a href="/sewa/{{ $slugA ?? strtolower(str_replace(' ', '-', $vehicleA ?? 'kendaraan-a')) }}" class="bg-white border border-stone-200 rounded-2xl p-6 text-center card-lift block">
                <div class="text-3xl mb-2">🚗</div>
                <h3 class="font-bold text-stone-900 mb-1">{{ $vehicleA ?? 'Kendaraan A' }}</h3>
                <p class="text-sm text-brand-600 font-semibold">Lihat Detail →</p>
            </a>
            <a href="/sewa/{{ $slugB ?? strtolower(str_replace(' ', '-', $vehicleB ?? 'kendaraan-b')) }}" class="bg-white border border-stone-200 rounded-2xl p-6 text-center card-lift block">
                <div class="text-3xl mb-2">🚙</div>
                <h3 class="font-bold text-stone-900 mb-1">{{ $vehicleB ?? 'Kendaraan B' }}</h3>
                <p class="text-sm text-brand-600 font-semibold">Lihat Detail →</p>
            </a>
        </div>
    </div>
</section>
@endsection

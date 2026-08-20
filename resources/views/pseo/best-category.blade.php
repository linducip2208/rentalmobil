@extends('pseo._layout')

@section('content')
<section class="py-16 lg:py-24">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="font-bold text-4xl lg:text-5xl text-stone-900 mb-4">
            Best {{ $category->name }}{{ $year ? " {$year}" : '' }} — Top Pilihan Sewa
        </h1>
        <p class="text-lg text-stone-500 mb-12">
            Daftar terbaik {{ strtolower($category->name) }}{{ $year ? " tahun {$year}" : '' }} untuk disewa di RentalMobil. Spesifikasi, harga, dan ulasan lengkap.
        </p>

        @if($vehicles->isEmpty())
            <div class="bg-stone-100 rounded-2xl p-12 text-center">
                <div class="text-4xl mb-4">🚗</div>
                <p class="text-stone-500">Belum ada kendaraan di kategori ini untuk tahun {{ $year ?? 'semua tahun' }}.</p>
                <a href="/sewa-mobil" class="mt-4 inline-block px-6 py-2 bg-brand-600 text-white rounded-lg font-semibold hover:bg-brand-700 transition-all">Lihat Semua Kendaraan</a>
            </div>
        @else
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
                @foreach($vehicles as $vehicle)
                    <a href="/sewa/{{ $vehicle->slug }}" class="bg-white border border-stone-200 rounded-2xl overflow-hidden card-lift block">
                        <div class="h-48 bg-stone-100 flex items-center justify-center">
                            @if($vehicle->photo_url)
                                <img src="{{ $vehicle->photo_url }}" alt="{{ $vehicle->name }}" class="w-full h-full object-cover">
                            @else
                                <span class="text-5xl">🚗</span>
                            @endif
                        </div>
                        <div class="p-5">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-xs font-semibold text-brand-600 bg-brand-50 px-2 py-0.5 rounded">{{ $category->name }}</span>
                                @if($vehicle->brand)
                                    <span class="text-xs text-stone-400">{{ $vehicle->brand->name }}</span>
                                @endif
                            </div>
                            <h2 class="font-bold text-lg text-stone-900 mb-1">{{ $vehicle->name }}</h2>
                            <p class="text-sm text-stone-500 mb-3">{{ $vehicle->year }} · {{ $vehicle->transmission }} · {{ $vehicle->seat_count }} kursi</p>
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-brand-700">Rp {{ number_format((float) $vehicle->daily_rate, 0, ',', '.') }}<span class="text-xs font-normal text-stone-400">/hari</span></span>
                                <span class="text-xs text-stone-400">{{ $vehicle->location?->name ?? '-' }}</span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif

        <div class="bg-brand-50 border border-brand-200 rounded-2xl p-8">
            <h2 class="font-bold text-xl text-stone-900 mb-3">Mengapa Sewa di RentalMobil?</h2>
            <div class="grid sm:grid-cols-3 gap-4 text-sm text-stone-600">
                <div>✅ Harga transparan tanpa biaya tersembunyi</div>
                <div>✅ Armada terawat dengan servis berkala</div>
                <div>✅ Booking online mudah & cepat</div>
                <div>✅ Tersedia sopir profesional</div>
                <div>✅ Asuransi kendaraan lengkap</div>
                <div>✅ Pengantaran ke lokasi Anda</div>
            </div>
        </div>
    </div>
</section>
@endsection

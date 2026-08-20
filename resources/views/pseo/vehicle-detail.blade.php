@extends('pseo._layout')

@section('content')
<section class="py-16 lg:py-24">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Breadcrumb --}}
        <nav class="flex items-center gap-2 text-sm text-stone-400 mb-8">
            <a href="/" class="hover:text-brand-600 transition-colors">Beranda</a>
            <span>/</span>
            <a href="/sewa-mobil" class="hover:text-brand-600 transition-colors">Sewa Mobil</a>
            <span>/</span>
            <span class="text-stone-700">{{ $vehicle->name ?? 'Kendaraan' }}</span>
        </nav>

        <div class="grid lg:grid-cols-5 gap-10">
            {{-- Left: Vehicle Details --}}
            <div class="lg:col-span-3">
                <h1 class="font-bold text-3xl lg:text-4xl text-stone-900 mb-4">{{ $vehicle->name ?? 'Nama Kendaraan' }}</h1>
                <div class="flex items-center gap-3 mb-6">
                    <span class="px-3 py-1 bg-brand-50 text-brand-700 text-xs font-semibold rounded-lg">{{ $vehicle->category ?? 'SUV' }}</span>
                    <span class="text-sm text-stone-500">{{ $vehicle->year ?? '2024' }} &middot; {{ $vehicle->transmission ?? 'Automatic' }} &middot; {{ $vehicle->fuel ?? 'Bensin' }}</span>
                </div>

                {{-- Photo --}}
                <div class="aspect-[16/10] bg-gradient-to-br from-brand-50 to-brand-100 rounded-2xl flex items-center justify-center mb-8">
                    <span class="text-8xl">🚗</span>
                </div>

                {{-- Specs --}}
                <h2 class="font-bold text-xl text-stone-900 mb-4">Spesifikasi</h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-8">
                    @foreach([
                        ['label' => 'Tahun', 'value' => $vehicle->year ?? '2024', 'icon' => '📅'],
                        ['label' => 'Transmisi', 'value' => $vehicle->transmission ?? 'Automatic', 'icon' => '⚙️'],
                        ['label' => 'BBM', 'value' => $vehicle->fuel ?? 'Bensin', 'icon' => '⛽'],
                        ['label' => 'Kursi', 'value' => ($vehicle->seats ?? '4') . ' kursi', 'icon' => '💺'],
                        ['label' => 'Bagasi', 'value' => $vehicle->luggage ?? '3 koper', 'icon' => '🧳'],
                        ['label' => 'Kilometer', 'value' => $vehicle->mileage ?? '< 50.000 km', 'icon' => '🛣️'],
                    ] as $spec)
                    <div class="bg-white border border-stone-200 rounded-xl p-4">
                        <div class="text-lg mb-1">{{ $spec['icon'] }}</div>
                        <div class="text-xs text-stone-500">{{ $spec['label'] }}</div>
                        <div class="font-semibold text-stone-800 text-sm">{{ $spec['value'] }}</div>
                    </div>
                    @endforeach
                </div>

                {{-- Description --}}
                <h2 class="font-bold text-xl text-stone-900 mb-4">Deskripsi</h2>
                <div class="prose prose-stone prose-sm max-w-none mb-8">
                    <p>{{ $vehicle->description ?? 'Kendaraan ini merupakan pilihan ideal untuk perjalanan Anda. Dalam kondisi terawat, dilengkapi asuransi all-risk, dan siap menemani perjalanan di dalam maupun luar kota.' }}</p>
                </div>

                {{-- Includes --}}
                <h2 class="font-bold text-xl text-stone-900 mb-4">Termasuk dalam Sewa</h2>
                <div class="grid sm:grid-cols-2 gap-3 mb-8">
                    @foreach(['Asuransi all-risk', 'Bantuan darurat 24 jam', 'Pajak kendaraan', 'Surat jalan lengkap', 'GPS tracking', 'Roadside assistance'] as $item)
                    <div class="flex items-center gap-2 text-sm text-stone-700">
                        <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-xs">✓</span>
                        {{ $item }}
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Right: Booking Sidebar --}}
            <div class="lg:col-span-2">
                <div class="sticky top-20 bg-white border border-stone-200 rounded-2xl p-6 shadow-lg">
                    <div class="mb-4">
                        <div class="text-sm text-stone-500">Mulai dari</div>
                        <div class="text-3xl font-bold text-brand-600">Rp {{ number_format($vehicle->price_per_day ?? 350000, 0, ',', '.') }}<span class="text-base font-normal text-stone-500">/hari</span></div>
                    </div>
                    <div class="space-y-3 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-stone-700 mb-1">Tanggal Ambil</label>
                            <input type="date" class="w-full px-3 py-2.5 rounded-lg border border-stone-300 text-sm focus:ring-2 focus:ring-brand-100 focus:border-brand-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-stone-700 mb-1">Tanggal Kembali</label>
                            <input type="date" class="w-full px-3 py-2.5 rounded-lg border border-stone-300 text-sm focus:ring-2 focus:ring-brand-100 focus:border-brand-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-stone-700 mb-1">Lokasi Pengambilan</label>
                            <select class="w-full px-3 py-2.5 rounded-lg border border-stone-300 text-sm focus:ring-2 focus:ring-brand-100 focus:border-brand-500 outline-none">
                                <option>Airport / Bandara</option>
                                <option>Stasiun / Terminal</option>
                                <option>Hotel / Penginapan</option>
                                <option>Kantor RentalMobil</option>
                            </select>
                        </div>
                    </div>
                    <button class="w-full py-3 bg-gradient-to-r from-brand-600 to-brand-700 text-white font-bold text-sm rounded-xl hover:from-brand-700 hover:to-brand-800 transition-all shadow-lg shadow-brand-600/20">
                        Sewa Sekarang
                    </button>
                    <p class="text-xs text-stone-400 text-center mt-3">Konfirmasi instan via WhatsApp</p>

                    <div class="border-t border-stone-100 mt-6 pt-6">
                        <div class="flex items-center justify-between text-sm mb-2">
                            <span class="text-stone-500">Harga per hari</span>
                            <span class="font-semibold text-stone-800">Rp {{ number_format($vehicle->price_per_day ?? 350000, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex items-center justify-between text-sm mb-2">
                            <span class="text-stone-500">Asuransi</span>
                            <span class="font-semibold text-emerald-600">Termasuk</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-stone-500">Pajak</span>
                            <span class="font-semibold text-emerald-600">Termasuk</span>
                        </div>
                    </div>

                    <a href="https://wa.me/6281234567890?text=Halo, saya tertarik menyewa {{ $vehicle->name ?? 'kendaraan' }}" target="_blank" class="block w-full text-center py-3 mt-4 border-2 border-emerald-200 text-emerald-700 font-semibold text-sm rounded-xl hover:bg-emerald-50 transition-all">
                        💬 Chat WhatsApp
                    </a>
                </div>
            </div>
        </div>

        {{-- Related --}}
        @if(isset($relatedVehicles) && count($relatedVehicles))
        <div class="mt-16">
            <h2 class="font-bold text-2xl text-stone-900 mb-6">Kendaraan Serupa</h2>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($relatedVehicles as $related)
                <a href="/sewa/{{ $related->slug ?? $related['slug'] ?? '#' }}" class="bg-white border border-stone-200 rounded-2xl overflow-hidden card-lift block">
                    <div class="aspect-video bg-gradient-to-br from-brand-50 to-brand-100 flex items-center justify-center"><span class="text-4xl">🚗</span></div>
                    <div class="p-4">
                        <h3 class="font-bold text-stone-900">{{ $related->name ?? $related['name'] ?? '' }}</h3>
                        <p class="text-sm text-brand-600 font-semibold">Rp {{ number_format($related->price_per_day ?? $related['price_per_day'] ?? 0, 0, ',', '.') }}/hari</p>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</section>
@endsection

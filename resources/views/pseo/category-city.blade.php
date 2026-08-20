@extends('pseo._layout')

@section('content')
<section class="py-16 lg:py-24">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="font-bold text-4xl lg:text-5xl text-stone-900 mb-4">Sewa Mobil di {{ $city }}</h1>
        <p class="text-lg text-stone-500 mb-12">Temukan kendaraan terbaik untuk perjalanan Anda di {{ $city }}</p>

        <div class="prose prose-stone prose-lg max-w-none mb-12">
            <p>{{ $cityDescription ?? "Menyewa mobil di {$city} memberikan Anda kebebasan untuk menjelajahi kota dan sekitarnya dengan nyaman. {$city} adalah salah satu kota dengan permintaan rental mobil tertinggi di Indonesia, baik untuk kebutuhan wisata, bisnis, maupun perjalanan pribadi. Dengan armada kendaraan yang beragam, dari city car yang lincah hingga SUV yang tangguh, Anda dapat menemukan kendaraan yang sempurna sesuai dengan rencana perjalanan." }}</p>

            <p>Kami menyediakan berbagai pilihan kendaraan di {{ $city }} dengan kondisi terawat dan dilengkapi asuransi. Proses pemesanan mudah dan cepat — cukup pilih kendaraan, tentukan tanggal, dan lakukan pembayaran. Driver profesional juga tersedia jika Anda membutuhkan bantuan selama perjalanan di {{ $city }} dan sekitarnya.</p>
        </div>

        <h2 class="font-bold text-2xl text-stone-900 mb-6">Kendaraan Tersedia di {{ $city }}</h2>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
            @forelse($vehicles ?? [] as $vehicle)
            <a href="/sewa/{{ $vehicle->slug ?? $vehicle['slug'] ?? '#' }}" class="bg-white border border-stone-200 rounded-2xl overflow-hidden card-lift block">
                <div class="aspect-video bg-gradient-to-br from-brand-50 to-brand-100 flex items-center justify-center">
                    <span class="text-5xl">🚗</span>
                </div>
                <div class="p-5">
                    <h3 class="font-bold text-stone-900 mb-1">{{ $vehicle->name ?? $vehicle['name'] ?? 'Kendaraan' }}</h3>
                    <p class="text-sm text-stone-500 mb-3">{{ $vehicle->category ?? $vehicle['category'] ?? '—' }} &middot; {{ $vehicle->transmission ?? $vehicle['transmission'] ?? '—' }} &middot; {{ $vehicle->fuel ?? $vehicle['fuel'] ?? 'Bensin' }}</p>
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-brand-600">Rp {{ number_format($vehicle->price_per_day ?? $vehicle['price_per_day'] ?? 0, 0, ',', '.') }}/hari</span>
                        <span class="text-xs text-stone-400">{{ $vehicle->seats ?? $vehicle['seats'] ?? 4 }} kursi</span>
                    </div>
                </div>
            </a>
            @empty
            <div class="col-span-full text-center py-12 text-stone-400">
                <div class="text-4xl mb-3">🚗</div>
                <p>Armada untuk kota ini sedang dalam proses update. Silakan hubungi kami untuk informasi.</p>
            </div>
            @endforelse
        </div>

        <div class="bg-white border border-stone-200 rounded-2xl p-8 mb-12">
            <h2 class="font-bold text-xl text-stone-900 mb-4">Harga Sewa di {{ $city }}</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-stone-200">
                            <th class="text-left py-3 px-4 font-semibold text-stone-700">Kategori</th>
                            <th class="text-left py-3 px-4 font-semibold text-stone-700">Lepas Kunci</th>
                            <th class="text-left py-3 px-4 font-semibold text-stone-700">Dengan Driver</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        <tr><td class="py-3 px-4">City Car</td><td class="py-3 px-4">Rp 250.000/hari</td><td class="py-3 px-4">Rp 400.000/hari</td></tr>
                        <tr><td class="py-3 px-4">Sedan</td><td class="py-3 px-4">Rp 350.000/hari</td><td class="py-3 px-4">Rp 500.000/hari</td></tr>
                        <tr><td class="py-3 px-4">SUV</td><td class="py-3 px-4">Rp 500.000/hari</td><td class="py-3 px-4">Rp 650.000/hari</td></tr>
                        <tr><td class="py-3 px-4">MPV</td><td class="py-3 px-4">Rp 400.000/hari</td><td class="py-3 px-4">Rp 550.000/hari</td></tr>
                    </tbody>
                </table>
            </div>
            <p class="text-xs text-stone-400 mt-3">*Harga bervariasi tergantung jenis kendaraan dan musim</p>
        </div>

        <div class="bg-brand-600 rounded-2xl p-8 text-center text-white">
            <h3 class="font-bold text-2xl mb-2">Butuh Mobil di {{ $city }}?</h3>
            <p class="text-brand-100 mb-6">Pesan sekarang dan dapatkan harga terbaik</p>
            <a href="/" class="inline-flex items-center gap-2 px-6 py-3 bg-white text-brand-700 font-bold rounded-xl hover:bg-brand-50 transition-all">Pesan Sekarang →</a>
        </div>
    </div>
</section>
@endsection

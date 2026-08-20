@extends('pseo._layout')

@section('content')
<section class="py-16 lg:py-24">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="font-bold text-4xl lg:text-5xl text-stone-900 mb-6">Sewa Mobil Indonesia</h1>

        <div class="prose prose-stone prose-lg max-w-none mb-12">
            <p>Sewa mobil di Indonesia semakin mudah berkat kemajuan teknologi dan platform digital. Dengan banyaknya pilihan kendaraan yang tersedia, dari city car kompak hingga bus pariwisata berkapasitas besar, setiap orang dapat menemukan kendaraan yang sesuai dengan kebutuhan dan anggaran mereka. Industri rental mobil Indonesia telah berkembang pesat, melayani berbagai segmen pasar mulai dari wisatawan domestik yang ingin menjelajahi keindahan alam Nusantara, hingga pebisnis yang membutuhkan kendaraan operasional untuk kegiatan korporat.</p>

            <p>Keunggulan utama menyewa mobil dibandingkan menggunakan transportasi umum adalah fleksibilitas. Anda dapat menentukan jadwal perjalanan sendiri, berhenti di mana saja sesuai keinginan, dan menikmati perjalanan tanpa harus berdesakan dengan penumpang lain. Terlebih lagi, dengan adanya layanan driver profesional, Anda tidak perlu khawatir tentang rute atau kondisi jalan — cukup duduk manis dan nikmati perjalanan.</p>

            <p>Platform rental mobil modern seperti RentalMobil menawarkan berbagai kemudahan yang sebelumnya tidak tersedia. Mulai dari pemesanan online 24 jam, pembayaran digital yang aman, hingga pelacakan GPS real-time untuk memantau perjalanan. Semua proses dirancang untuk memberikan pengalaman yang seamless bagi pelanggan. Dengan sistem manajemen armada yang canggih, setiap kendaraan dipastikan dalam kondisi prima sebelum disewakan, dilengkapi dengan asuransi all-risk untuk ketenangan pikiran penyewa.</p>

            <p>Kota-kota besar seperti Jakarta, Bandung, Surabaya, Bali, Yogyakarta, dan Medan menjadi pusat permintaan terbesar untuk jasa rental mobil. Namun, layanan kini juga tersedia di kota-kota kecil dan destinasi wisata populer seperti Lombok, Bromo, dan Labuan Bajo. Ketersediaan armada yang luas memastikan bahwa di mana pun tujuan Anda, selalu ada kendaraan yang siap menemani perjalanan.</p>

            <p>Bagi Anda yang mencari alternatif kendaraan, kami menyediakan berbagai pilihan mulai dari SUV tangguh untuk medan off-road, sedan nyaman untuk perjalanan bisnis, MPV luas untuk keluarga, hingga van dan bus untuk rombongan besar. Setiap kategori kendaraan memiliki keunggulan masing-masing yang dirancang untuk memenuhi kebutuhan spesifik penyewa.</p>
        </div>

        <h2 class="font-bold text-2xl text-stone-900 mb-8">Kategori Kendaraan</h2>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-16">
            @foreach([
                ['name' => 'SUV', 'icon' => '🏔️', 'desc' => 'Kendaraan tangguh untuk medan off-road dan perjalanan jauh', 'slug' => 'suv'],
                ['name' => 'Sedan', 'icon' => '🏎️', 'desc' => 'Kendaraan nyaman dan irit untuk perjalanan bisnis', 'slug' => 'sedan'],
                ['name' => 'MPV', 'icon' => '👨‍👩‍👧‍👦', 'desc' => 'Kendaraan luas untuk keluarga dan rombongan', 'slug' => 'mpv'],
                ['name' => 'City Car', 'icon' => '🚗', 'desc' => 'Kendaraan kompak untuk mobilitas perkotaan', 'slug' => 'city-car'],
                ['name' => 'Pickup', 'icon' => '📦', 'desc' => 'Kendaraan niaga untuk angkutan barang', 'slug' => 'pickup'],
                ['name' => 'Bus Pariwisata', 'icon' => '🚌', 'desc' => 'Bus besar untuk rombongan wisata dan event', 'slug' => 'bus'],
            ] as $cat)
            <a href="/sewa-mobil/{{ $cat['slug'] }}" class="bg-white border border-stone-200 rounded-2xl p-6 card-lift block">
                <div class="text-4xl mb-3">{{ $cat['icon'] }}</div>
                <h3 class="font-bold text-lg text-stone-900 mb-1">{{ $cat['name'] }}</h3>
                <p class="text-sm text-stone-500">{{ $cat['desc'] }}</p>
            </a>
            @endforeach
        </div>

        <h2 class="font-bold text-2xl text-stone-900 mb-6">FAQ — Sewa Mobil</h2>
        <div class="space-y-4 mb-12" x-data="{ open: null }">
            @foreach([
                ['q' => 'Bagaimana cara menyewa mobil di RentalMobil?', 'a' => 'Pilih kendaraan yang diinginkan dari katalog kami, tentukan tanggal dan lokasi pengambilan, lalu lakukan pembayaran online. Konfirmasi akan dikirim via WhatsApp dan email dalam hitungan menit.'],
                ['q' => 'Apakah saya perlu SIM untuk menyewa mobil?', 'a' => 'Ya, Anda wajib memiliki SIM yang masih berlaku sesuai dengan kelas kendaraan yang disewa. Kami akan memverifikasi SIM saat pengambilan kendaraan.'],
                ['q' => 'Bagaimana jika kendaraan mogok di jalan?', 'a' => 'Kami menyediakan layanan bantuan darurat 24 jam. Hubungi nomor darurat kami dan tim akan segera memberikan bantuan, baik berupa perbaikan di tempat maupun penggantian kendaraan.'],
                ['q' => 'Apakah harga sudah termasuk asuransi?', 'a' => 'Ya, semua kendaraan kami sudah dilindungi asuransi all-risk. Cacat bodi kecil (≤ 30cm) tidak perlu diklaim. Untuk kerusakan lebih besar, proses klaim akan dibantu oleh tim kami.'],
                ['q' => 'Bisakah saya menyewa mobil dengan driver?', 'a' => 'Tentu! Kami menyediakan driver profesional bersertifikat. Tarif driver flat per hari, tidak meteran. Driver kami hafal rute wisata dan perkotaan di seluruh Indonesia.'],
                ['q' => 'Apakah ada diskon untuk penyewaan jangka panjang?', 'a' => 'Ya, kami memberikan diskon khusus untuk penyewaan mingguan (7 hari+) dan bulanan (30 hari+). Hubungi tim kami untuk penawaran harga terbaik.'],
            ] as $i => $faq)
            <div class="bg-white border border-stone-200 rounded-xl overflow-hidden">
                <button class="w-full text-left px-6 py-4 flex items-center justify-between font-semibold text-stone-800 hover:bg-stone-50 transition-colors" @click="open = open === {{ $i }} ? null : {{ $i }}">
                    <span>{{ $faq['q'] }}</span>
                    <svg class="w-5 h-5 text-stone-400 shrink-0 transition-transform duration-200" :class="open === {{ $i }} ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div class="px-6 pb-4 text-stone-600 text-sm leading-relaxed" x-show="open === {{ $i }}" x-collapse x-cloak>
                    {{ $faq['a'] }}
                </div>
            </div>
            @endforeach
        </div>

        <div class="bg-brand-600 rounded-2xl p-8 text-center text-white">
            <h3 class="font-bold text-2xl mb-2">Siap Menyewa Mobil?</h3>
            <p class="text-brand-100 mb-6">Temukan kendaraan terbaik untuk perjalanan Anda</p>
            <a href="/" class="inline-flex items-center gap-2 px-6 py-3 bg-white text-brand-700 font-bold rounded-xl hover:bg-brand-50 transition-all">Browse Mobil →</a>
        </div>
    </div>
</section>
@endsection

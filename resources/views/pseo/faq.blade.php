@extends('pseo._layout')

@section('content')
<section class="py-16 lg:py-24">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="font-bold text-4xl lg:text-5xl text-stone-900 mb-4">FAQ</h1>
        <p class="text-lg text-stone-500 mb-12">Pertanyaan yang sering diajukan tentang layanan rental mobil kami</p>

        <div class="space-y-4" x-data="{ open: null }">
            @foreach([
                ['q' => 'Bagaimana cara menyewa mobil di RentalMobil?', 'a' => 'Pilih kendaraan yang diinginkan dari katalog kami, tentukan tanggal dan lokasi pengambilan, lalu lakukan pembayaran online. Konfirmasi akan dikirim via WhatsApp dan email dalam hitungan menit.'],
                ['q' => 'Apakah saya perlu SIM untuk menyewa mobil?', 'a' => 'Ya, Anda wajib memiliki SIM yang masih berlaku sesuai dengan kelas kendaraan yang disewa. Kami akan memverifikasi SIM saat pengambilan kendaraan.'],
                ['q' => 'Bagaimana jika kendaraan mogok di jalan?', 'a' => 'Kami menyediakan layanan bantuan darurat 24 jam. Hubungi nomor darurat kami dan tim akan segera memberikan bantuan, baik berupa perbaikan di tempat maupun penggantian kendaraan.'],
                ['q' => 'Apakah harga sudah termasuk asuransi?', 'a' => 'Ya, semua kendaraan kami sudah dilindungi asuransi all-risk. Cacat bodi kecil (≤ 30cm) tidak perlu diklaim. Untuk kerusakan lebih besar, proses klaim akan dibantu oleh tim kami.'],
                ['q' => 'Bisakah saya menyewa mobil dengan driver?', 'a' => 'Tentu! Kami menyediakan driver profesional bersertifikat. Tarif driver flat per hari, tidak meteran. Driver kami hafal rute wisata dan perkotaan di seluruh Indonesia.'],
                ['q' => 'Apakah ada diskon untuk penyewaan jangka panjang?', 'a' => 'Ya, kami memberikan diskon khusus untuk penyewaan mingguan (7 hari+) dan bulanan (30 hari+). Hubungi tim kami untuk penawaran harga terbaik.'],
                ['q' => 'Metode pembayaran apa yang diterima?', 'a' => 'Kami menerima transfer bank (BCA, Mandiri, BRI, BNI), kartu kredit/debit, e-wallet (GoPay, OVO, Dana, ShopeePay), dan tunai saat pengambilan kendaraan.'],
                ['q' => 'Bisakah saya membatalkan pesanan?', 'a' => 'Pembatalan gratis jika dilakukan minimal 24 jam sebelum jadwal pengambilan. Pembatalan kurang dari 24 jam akan dikenakan biaya pembatalan sebesar 50% dari harga sewa.'],
                ['q' => 'Apakah tersedia layanan antar jemput kendaraan?', 'a' => 'Ya, kami menyediakan layanan antar jemput kendaraan ke lokasi Anda (bandara, hotel, stasiun) dengan biaya tambahan sesuai jarak.'],
                ['q' => 'Bagaimana jika saya terlambat mengembalikan kendaraan?', 'a' => 'Keterlambatan pengembalian akan dikenakan biaya tambahan sebesar 50% dari harga sewa per jam untuk 3 jam pertama, dan harga penuh per hari untuk keterlambatan lebih dari 3 jam.'],
                ['q' => 'Apakah ada batasan kilometer?', 'a' => 'Paket lepas kunci memiliki batasan 250 km per hari. Melebihi batasan akan dikenakan biaya tambahan Rp 2.500/km. Paket dengan driver tidak memiliki batasan kilometer.'],
                ['q' => 'Bagaimana cara menjadi pelanggan tetap?', 'a' => 'Daftar akun di portal pelanggan kami dan lakukan minimal 3 kali penyewaan. Anda akan otomatis mendapatkan status Gold dengan diskon 10% untuk penyewaan berikutnya.'],
            ] as $i => $faq)
            <div class="bg-white border border-stone-200 rounded-xl overflow-hidden transition-all {{ $loop->first ? 'border-brand-200' : '' }}">
                <button class="w-full text-left px-6 py-5 flex items-center justify-between font-semibold text-stone-800 hover:bg-stone-50 transition-colors gap-4" @click="open = open === {{ $i }} ? null : {{ $i }}">
                    <span class="text-sm sm:text-base">{{ $faq['q'] }}</span>
                    <svg class="w-5 h-5 text-stone-400 shrink-0 transition-transform duration-200" :class="open === {{ $i }} ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div class="px-6 pb-5 text-stone-600 text-sm leading-relaxed" x-show="open === {{ $i }}" x-collapse x-cloak>
                    {{ $faq['a'] }}
                </div>
            </div>
            @endforeach
        </div>

        <div class="mt-12 bg-brand-50 border border-brand-200 rounded-2xl p-8 text-center">
            <h3 class="font-bold text-xl text-stone-900 mb-2">Masih Punya Pertanyaan?</h3>
            <p class="text-stone-600 mb-4">Tim kami siap membantu Anda 24/7</p>
            <div class="flex flex-col sm:flex-row justify-center gap-3">
                <a href="https://wa.me/6281234567890" target="_blank" class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-emerald-500 text-white font-semibold rounded-xl hover:bg-emerald-600 transition-all">💬 Chat WhatsApp</a>
                <a href="/contact" class="inline-flex items-center justify-center gap-2 px-6 py-3 border-2 border-brand-200 text-brand-700 font-semibold rounded-xl hover:bg-brand-50 transition-all">✉️ Kirim Email</a>
            </div>
        </div>
    </div>
</section>
@endsection

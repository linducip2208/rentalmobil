@extends('pseo._layout')

@section('content')
<section class="py-16 lg:py-24">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="font-bold text-4xl lg:text-5xl text-stone-900 mb-4">Dokumentasi RentalMobil</h1>
        <p class="text-lg text-stone-500 mb-12">Panduan lengkap menggunakan aplikasi rental mobil kami</p>

        {{-- Jump Nav --}}
        <div class="sticky top-16 z-40 bg-white/80 backdrop-blur-xl border border-stone-200 rounded-xl p-3 mb-12 flex flex-wrap gap-2 text-sm font-medium">
            <a href="#demo-accounts" class="px-3 py-1.5 rounded-lg hover:bg-brand-50 text-stone-600 hover:text-brand-600 transition-colors">Akun Demo</a>
            <a href="#structure" class="px-3 py-1.5 rounded-lg hover:bg-brand-50 text-stone-600 hover:text-brand-600 transition-colors">Struktur Menu</a>
            <a href="#tutorial" class="px-3 py-1.5 rounded-lg hover:bg-brand-50 text-stone-600 hover:text-brand-600 transition-colors">Tutorial</a>
            <a href="#features" class="px-3 py-1.5 rounded-lg hover:bg-brand-50 text-stone-600 hover:text-brand-600 transition-colors">Fitur Lengkap</a>
        </div>

        {{-- Demo Accounts --}}
        <div id="demo-accounts" class="mb-16">
            <h2 class="font-bold text-2xl text-stone-900 mb-6">Akun Demo</h2>
            <div class="bg-white border border-stone-200 rounded-2xl overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-stone-900 text-white">
                                <th class="px-6 py-4 text-left font-semibold uppercase text-xs">Role</th>
                                <th class="px-6 py-4 text-left font-semibold uppercase text-xs">Email</th>
                                <th class="px-6 py-4 text-left font-semibold uppercase text-xs">Password</th>
                                <th class="px-6 py-4 text-left font-semibold uppercase text-xs hidden sm:table-cell">Cakupan Akses</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100">
                            @foreach([
                                ['role' => 'Owner', 'email' => 'owner@rentalmobil.test', 'scope' => 'Semua akses, pengaturan sistem, laporan keuangan', 'color' => 'brand'],
                                ['role' => 'Manager', 'email' => 'manager@rentalmobil.test', 'scope' => 'Operasional, booking, armada, laporan', 'color' => 'emerald'],
                                ['role' => 'Admin', 'email' => 'admin@rentalmobil.test', 'scope' => 'Data master, booking, pelanggan', 'color' => 'amber'],
                                ['role' => 'Kasir', 'email' => 'kasir@rentalmobil.test', 'scope' => 'Pembayaran, invoice, struk', 'color' => 'violet'],
                                ['role' => 'Driver', 'email' => 'driver@rentalmobil.test', 'scope' => 'Jadwal pickup/dropoff, GPS tracking', 'color' => 'rose'],
                                ['role' => 'Pelanggan', 'email' => 'pelanggan@rentalmobil.test', 'scope' => 'Portal booking, histori, invoice', 'color' => 'cyan'],
                            ] as $acc)
                            <tr class="hover:bg-stone-50 transition-colors">
                                <td class="px-6 py-4"><span class="inline-flex items-center gap-1.5 font-semibold"><span class="w-2 h-2 bg-{{ $acc['color'] }}-500 rounded-full"></span>{{ $acc['role'] }}</span></td>
                                <td class="px-6 py-4 font-mono text-stone-600">{{ $acc['email'] }}</td>
                                <td class="px-6 py-4 font-mono text-stone-600">password</td>
                                <td class="px-6 py-4 text-stone-500 hidden sm:table-cell">{{ $acc['scope'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="mt-4 flex gap-3">
                <a href="/admin/login" class="px-5 py-2.5 bg-brand-600 text-white text-sm font-semibold rounded-xl hover:bg-brand-700 transition-all">Masuk Admin Panel</a>
                <a href="/portal/login" class="px-5 py-2.5 border-2 border-stone-200 text-stone-700 text-sm font-semibold rounded-xl hover:bg-stone-50 transition-all">Masuk Portal Pelanggan</a>
            </div>
        </div>

        {{-- Structure --}}
        <div id="structure" class="mb-16">
            <h2 class="font-bold text-2xl text-stone-900 mb-6">Struktur Menu Admin</h2>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach([
                    ['group' => '🏪 Master Data', 'items' => ['Kategori Kendaraan', 'Kendaraan', 'Gudang', 'Pelanggan', 'Supplier', 'Metode Pembayaran', 'Tarif & Harga']],
                    ['group' => '📋 Transaksi', 'items' => ['Booking', 'Rental Order', 'Invoice', 'Pembayaran', 'Pengembalian']],
                    ['group' => '👨‍✈️ Operasional', 'items' => ['Jadwal Driver', 'GPS Tracking', 'Maintenance Kendaraan', 'Inspeksi', 'Asuransi & Klaim']],
                    ['group' => '📊 Laporan', 'items' => ['Overview', 'Penjualan', 'Keuangan', 'Utilisasi Armada', 'Inspeksi']],
                    ['group' => '👥 Marketing', 'items' => ['Blog Post', 'Kategori Blog', 'Promo', 'Newsletter']],
                    ['group' => '⚙️ Sistem', 'items' => ['User & Role', 'Audit Log', 'Pengaturan', 'Integrasi', 'Webhook']],
                ] as $nav)
                <div class="bg-white border border-stone-200 rounded-xl p-5">
                    <h3 class="font-bold text-stone-900 mb-3">{{ $nav['group'] }}</h3>
                    <ul class="space-y-1.5">
                        @foreach($nav['items'] as $item)
                        <li class="text-sm text-stone-600 flex items-center gap-2">
                            <span class="w-1.5 h-1.5 bg-brand-400 rounded-full"></span>{{ $item }}
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Tutorial --}}
        <div id="tutorial" class="mb-16">
            <h2 class="font-bold text-2xl text-stone-900 mb-6">Tutorial — Alur Bisnis</h2>
            <div class="space-y-8">
                @foreach([
                    ['phase' => 'Fase 1: Setup Awal', 'steps' => ['Login ke admin panel dengan akun owner', 'Konfigurasi nama bisnis, logo, dan kontak di Pengaturan', 'Tambahkan metode pembayaran (transfer, kartu kredit, e-wallet)', 'Setup driver dan jadwal kerja']],
                    ['phase' => 'Fase 2: Master Data', 'steps' => ['Tambahkan kategori kendaraan (SUV, Sedan, MPV, dll)', 'Input data kendaraan: nama, tahun, plat, harga, foto', 'Atur harga per kategori dan diskon jangka panjang', 'Tambahkan data pelanggan dari hasil registrasi']],
                    ['phase' => 'Fase 3: Transaksi Harian', 'steps' => ['Terima booking dari portal pelanggan', 'Verifikasi ketersediaan kendaraan', 'Buat rental order dari booking yang terkonfirmasi', 'Generate invoice dan kirim ke pelanggan', 'Proses pembayaran dan cetak struk']],
                    ['phase' => 'Fase 4: Operasional', 'steps' => ['Assign driver untuk setiap rental order', 'Lakukan inspeksi kendaraan sebelum diserahterimakan', 'Pantau GPS tracking selama masa sewa', 'Proses pengembalian kendaraan', 'Catat kondisi kendaraan pasca-sewa']],
                    ['phase' => 'Fase 5: Laporan & Analisis', 'steps' => ['Cek dashboard overview untuk ringkasan harian', 'Lihat laporan penjualan per periode', 'Analisis utilisasi armada', 'Export laporan keuangan untuk akuntansi']],
                ] as $fase)
                <div class="bg-white border border-stone-200 rounded-2xl p-6">
                    <h3 class="font-bold text-lg text-stone-900 mb-4">{{ $fase['phase'] }}</h3>
                    <ol class="space-y-2">
                        @foreach($fase['steps'] as $step)
                        <li class="flex items-start gap-3 text-sm text-stone-700">
                            <span class="w-6 h-6 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center text-xs font-bold shrink-0">{{ $loop->iteration }}</span>
                            {{ $step }}
                        </li>
                        @endforeach
                    </ol>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Features --}}
        <div id="features" class="mb-16">
            <h2 class="font-bold text-2xl text-stone-900 mb-6">Fitur Lengkap</h2>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach([
                    ['title' => 'Katalog Kendaraan', 'desc' => 'Filter berdasarkan tipe, harga, kota. Foto 360°, status real-time.', 'color' => 'brand'],
                    ['title' => 'Booking Online', 'desc' => 'Proses 3 menit, konfirmasi instan via WhatsApp.', 'color' => 'emerald'],
                    ['title' => 'Harga Transparan', 'desc' => 'Harga final termasuk asuransi & pajak. Tidak ada biaya tersembunyi.', 'color' => 'amber'],
                    ['title' => 'Asuransi All-Risk', 'desc' => 'Semua kendaraan dilindungi. Klaim cepat & mudah.', 'color' => 'violet'],
                    ['title' => 'Driver Profesional', 'desc' => 'Sertifikat, hafal rute, tarif flat per hari.', 'color' => 'rose'],
                    ['title' => 'GPS Real-time', 'desc' => 'Live tracking, geofence, history route, alert kecepatan.', 'color' => 'cyan'],
                    ['title' => 'Dashboard Per-Role', 'desc' => 'Widget berbeda untuk Owner, Manager, Kasir, Driver.', 'color' => 'brand'],
                    ['title' => 'Invoice Otomatis', 'desc' => 'Generate PDF, kirim otomatis, track status bayar.', 'color' => 'emerald'],
                    ['title' => 'Portal Pelanggan', 'desc' => 'Self-service booking, histori, invoice download.', 'color' => 'amber'],
                ] as $feat)
                <div class="bg-white border border-stone-200 rounded-2xl overflow-hidden card-lift">
                    <div class="h-32 bg-gradient-to-br from-{{ $feat['color'] }}-50 to-{{ $feat['color'] }}-100 flex items-center justify-center">
                        <span class="text-4xl">🚗</span>
                    </div>
                    <div class="p-5">
                        <h3 class="font-bold text-stone-900 mb-1">{{ $feat['title'] }}</h3>
                        <p class="text-sm text-stone-500">{{ $feat['desc'] }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- CTA --}}
        <div class="bg-gradient-to-r from-brand-600 to-brand-700 rounded-2xl p-10 text-center text-white">
            <h2 class="font-bold text-3xl mb-3">Siap Mencoba?</h2>
            <p class="text-brand-100 mb-6 max-w-lg mx-auto">Masuk ke admin panel dengan akun demo dan jelajahi semua fitur yang tersedia</p>
            <a href="/admin/login" class="inline-flex items-center gap-2 px-8 py-3.5 bg-white text-brand-700 font-bold rounded-xl hover:bg-brand-50 transition-all shadow-lg">Masuk Admin Panel →</a>
        </div>
    </div>
</section>
@endsection

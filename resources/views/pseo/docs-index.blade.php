@extends('layouts.public')

@section('content')
<section class="py-16 lg:py-24">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-12 reveal">
            <h1 class="font-bold text-4xl lg:text-5xl text-stone-900 mb-4">Dokumentasi RentalMobil</h1>
            <p class="text-lg text-stone-500">Panduan lengkap menggunakan sistem rental mobil kami</p>
        </div>

        {{-- Jump Nav --}}
        <div class="sticky top-16 z-40 bg-white/80 backdrop-blur-xl border border-stone-200 rounded-xl p-3 mb-12 flex flex-wrap gap-2 text-sm font-medium reveal">
            <a href="#demo-accounts" class="px-3 py-1.5 rounded-lg hover:bg-indigo-50 text-stone-600 hover:text-indigo-600 transition">Akun Demo</a>
            <a href="#structure" class="px-3 py-1.5 rounded-lg hover:bg-indigo-50 text-stone-600 hover:text-indigo-600 transition">Struktur Menu</a>
            <a href="#tutorial" class="px-3 py-1.5 rounded-lg hover:bg-indigo-50 text-stone-600 hover:text-indigo-600 transition">Tutorial</a>
            <a href="#features" class="px-3 py-1.5 rounded-lg hover:bg-indigo-50 text-stone-600 hover:text-indigo-600 transition">Fitur Lengkap</a>
        </div>

        {{-- Demo Accounts --}}
        <div id="demo-accounts" class="mb-16 reveal">
            <h2 class="font-bold text-2xl text-stone-900 mb-6">Akun Demo</h2>
            <div class="bg-white border border-stone-200 rounded-2xl overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-stone-900 text-white">
                                <th class="px-6 py-4 text-left font-semibold uppercase text-xs tracking-wide">Role</th>
                                <th class="px-6 py-4 text-left font-semibold uppercase text-xs tracking-wide">Email</th>
                                <th class="px-6 py-4 text-left font-semibold uppercase text-xs tracking-wide">Password</th>
                                <th class="px-6 py-4 text-left font-semibold uppercase text-xs tracking-wide hidden sm:table-cell">Cakupan Akses</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100">
                            @foreach([
                                ['role' => 'Owner', 'email' => 'admin@rentalmobil.test', 'scope' => 'Semua akses, pengaturan sistem, laporan keuangan', 'dot' => 'bg-indigo-500'],
                                ['role' => 'Manager', 'email' => 'manager@rentalmobil.test', 'scope' => 'Operasional, booking, armada, laporan', 'dot' => 'bg-emerald-500'],
                                ['role' => 'Admin', 'email' => 'admin2@rentalmobil.test', 'scope' => 'Data master, booking, pelanggan', 'dot' => 'bg-amber-500'],
                                ['role' => 'Kasir', 'email' => 'kasir@rentalmobil.test', 'scope' => 'Pembayaran, invoice, struk', 'dot' => 'bg-violet-500'],
                                ['role' => 'Driver', 'email' => 'driver@rentalmobil.test', 'scope' => 'Jadwal pickup/dropoff, GPS tracking', 'dot' => 'bg-rose-500'],
                            ] as $acc)
                            <tr class="hover:bg-stone-50 transition">
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-1.5 font-semibold">
                                        <span class="w-2 h-2 {{ $acc['dot'] }} rounded-full"></span>{{ $acc['role'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 font-mono text-stone-600">{{ $acc['email'] }}</td>
                                <td class="px-6 py-4 font-mono text-stone-600">password</td>
                                <td class="px-6 py-4 text-stone-500 hidden sm:table-cell">{{ $acc['scope'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="mt-4">
                <a href="/admin/login" class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 text-white text-sm font-semibold rounded-xl hover:bg-indigo-700 transition shadow-md shadow-indigo-500/25">Masuk Admin Panel →</a>
            </div>
        </div>

        {{-- Structure --}}
        <div id="structure" class="mb-16 reveal">
            <h2 class="font-bold text-2xl text-stone-900 mb-6">Struktur Menu Admin</h2>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach([
                    ['group' => '🚗 Master Data', 'items' => ['Brand', 'Kategori', 'Kendaraan', 'Lokasi', 'Driver', 'Pelanggan', 'Dokumen Pelanggan', 'Metode Pembayaran', 'Spare Part']],
                    ['group' => '📋 Penjualan', 'items' => ['Booking', 'Rental Order', 'Invoice', 'Kontrak', 'Kuotation', 'Pembayaran']],
                    ['group' => '💰 Keuangan', 'items' => ['CoA', 'Jurnal', 'Expense', 'Kategori Expense', 'Transfer', 'Rekening Bank']],
                    ['group' => '🔧 Operasional', 'items' => ['Serah Terima', 'Pengembalian', 'Pengiriman', 'Maintenance', 'GPS Log', 'KM Log', 'Bahan Bakar']],
                    ['group' => '🛡️ Security', 'items' => ['Blacklist', 'Watch List', 'Investigation', 'Police Report', 'Insurance', 'Trust Score']],
                    ['group' => '📢 Marketing', 'items' => ['Blog Post', 'FAQ', 'Testimoni', 'Promo Voucher']],
                    ['group' => '⚙️ Sistem', 'items' => ['User', 'Addon', 'Layanan', 'Pengaturan']],
                ] as $nav)
                <div class="bg-white border border-stone-200 rounded-xl p-5 card-lift">
                    <h3 class="font-bold text-stone-900 mb-3">{{ $nav['group'] }}</h3>
                    <ul class="space-y-1.5">
                        @foreach($nav['items'] as $item)
                        <li class="text-sm text-stone-600 flex items-center gap-2">
                            <span class="w-1.5 h-1.5 bg-indigo-400 rounded-full"></span>{{ $item }}
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Tutorial --}}
        <div id="tutorial" class="mb-16 reveal">
            <h2 class="font-bold text-2xl text-stone-900 mb-6">Tutorial — Alur Bisnis</h2>
            <div class="space-y-8">
                @foreach([
                    ['phase' => 'Fase 1: Setup Awal', 'color' => 'indigo', 'steps' => [
                        'Login ke admin panel dengan akun owner (admin@rentalmobil.test)',
                        'Konfigurasi nama bisnis dan kontak di Pengaturan Sistem',
                        'Tambahkan metode pembayaran (transfer bank, kartu kredit, e-wallet)',
                        'Setup akun driver dan jadwal kerja'
                    ]],
                    ['phase' => 'Fase 2: Master Data', 'color' => 'emerald', 'steps' => [
                        'Tambahkan brand kendaraan (Toyota, Honda, dll)',
                        'Buat kategori kendaraan (SUV, Sedan, MPV, Hatchback)',
                        'Input data kendaraan: nama, tahun, plat, harga sewa, foto',
                        'Atur lokasi/gudang kendaraan',
                        'Tambahkan data pelanggan'
                    ]],
                    ['phase' => 'Fase 3: Transaksi Harian', 'color' => 'amber', 'steps' => [
                        'Terima booking dari pelanggan',
                        'Verifikasi ketersediaan kendaraan via Availability Engine',
                        'Buat rental order dari booking yang terkonfirmasi',
                        'Generate invoice otomatis dari rental order',
                        'Proses pembayaran dan catat ke sistem'
                    ]],
                    ['phase' => 'Fase 4: Operasional', 'color' => 'violet', 'steps' => [
                        'Assign driver untuk setiap rental order',
                        'Lakukan serah terima kendaraan (Handover Record)',
                        'Pantau GPS tracking selama masa sewa',
                        'Catat log KM dan bahan bakar',
                        'Proses pengembalian kendaraan (Return Record)',
                        'Inspeksi kondisi kendaraan pasca-sewa'
                    ]],
                    ['phase' => 'Fase 5: Keuangan & Laporan', 'color' => 'rose', 'steps' => [
                        'Catat jurnal otomatis dari setiap transaksi',
                        'Kelola expense dan kategori pengeluaran',
                        'Lihat laporan penjualan per periode',
                        'Analisis utilisasi armada',
                        'Cek laporan keuangan (P&L, COA)',
                        'Export laporan untuk akuntansi'
                    ]],
                ] as $fase)
                <div class="bg-white border border-stone-200 rounded-2xl p-6 card-lift">
                    <h3 class="font-bold text-lg text-stone-900 mb-4">{{ $fase['phase'] }}</h3>
                    <ol class="space-y-3">
                        @foreach($fase['steps'] as $step)
                        <li class="flex items-start gap-3 text-sm text-stone-700">
                            <span class="w-6 h-6 rounded-full bg-{{ $fase['color'] }}-100 text-{{ $fase['color'] }}-700 flex items-center justify-center text-xs font-bold shrink-0">{{ $loop->iteration }}</span>
                            {{ $step }}
                        </li>
                        @endforeach
                    </ol>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Features --}}
        <div id="features" class="mb-16 reveal">
            <h2 class="font-bold text-2xl text-stone-900 mb-6">Fitur Lengkap</h2>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach([
                    ['icon' => '🚗', 'title' => 'Manajemen Armada', 'desc' => 'Kelola kendaraan, brand, kategori, status, GPS, maintenance dalam satu tempat.', 'gradient' => 'from-indigo-50 to-violet-50'],
                    ['icon' => '📋', 'title' => 'Booking & Rental', 'desc' => 'Alur booking → order → invoice → pembayaran yang terintegrasi penuh.', 'gradient' => 'from-emerald-50 to-teal-50'],
                    ['icon' => '💰', 'title' => 'Akuntansi Bawaan', 'desc' => 'CoA, jurnal otomatis, expense tracking, laporan P&L tanpa software eksternal.', 'gradient' => 'from-amber-50 to-orange-50'],
                    ['icon' => '🔧', 'title' => 'Operasional Lengkap', 'desc' => 'Serah terima, pengembalian, pengiriman, GPS log, KM log, bahan bakar.', 'gradient' => 'from-violet-50 to-purple-50'],
                    ['icon' => '🛡️', 'title' => 'Security & Anti-Fraud', 'desc' => 'Blacklist, watch list, investigation, police report, asuransi, trust score.', 'gradient' => 'from-rose-50 to-pink-50'],
                    ['icon' => '📊', 'title' => 'Dashboard Per-Role', 'desc' => 'Widget berbeda untuk Owner, Manager, Admin, Kasir — data yang relevan per role.', 'gradient' => 'from-sky-50 to-cyan-50'],
                    ['icon' => '📈', 'title' => 'Laporan Bisnis', 'desc' => 'Laporan penjualan, keuangan, dan operasional dengan chart interaktif.', 'gradient' => 'from-indigo-50 to-blue-50'],
                    ['icon' => '📰', 'title' => 'Blog & SEO', 'desc' => 'Blog built-in, sitemap dinamis, robots.txt, IndexNow, PSEO routes.', 'gradient' => 'from-emerald-50 to-green-50'],
                    ['icon' => '⚡', 'title' => 'Otomatisasi', 'desc' => 'Scheduler otomatis: overdue escalation, reminder, backup database, IndexNow submit.', 'gradient' => 'from-amber-50 to-yellow-50'],
                ] as $feat)
                <div class="bg-white border border-stone-200 rounded-2xl overflow-hidden card-lift">
                    <div class="h-28 bg-gradient-to-br {{ $feat['gradient'] }} flex items-center justify-center">
                        <span class="text-4xl">{{ $feat['icon'] }}</span>
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
        <div class="bg-gradient-to-r from-indigo-600 to-violet-600 rounded-2xl p-10 text-center text-white reveal">
            <h2 class="font-bold text-3xl mb-3">Siap Mencoba?</h2>
            <p class="text-indigo-100 mb-6 max-w-lg mx-auto">Masuk ke admin panel dengan akun demo dan jelajahi semua fitur yang tersedia</p>
            <a href="/admin/login" class="inline-flex items-center gap-2 px-8 py-3.5 bg-white text-indigo-700 font-bold rounded-xl hover:bg-indigo-50 transition shadow-lg">Masuk Admin Panel →</a>
        </div>
    </div>
</section>
@endsection

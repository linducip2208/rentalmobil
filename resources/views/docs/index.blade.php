<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dokumentasi — RentalMobil</title>
    <meta name="description" content="Panduan lengkap menggunakan sistem manajemen rental mobil RentalMobil. Tutorial, fitur, dan akun demo.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace'],
                    },
                    colors: {
                        brand: {
                            50: '#eef2ff', 100: '#e0e7ff', 200: '#c7d2fe', 300: '#a5b4fc',
                            400: '#818cf8', 500: '#6366f1', 600: '#4f46e5', 700: '#4338ca',
                            800: '#3730a3', 900: '#312e81',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .reveal { opacity: 0; transform: translateY(20px); transition: opacity .5s ease, transform .5s cubic-bezier(.16,1,.3,1); }
        .reveal.visible { opacity: 1; transform: translateY(0); }
        .jump-nav-active { color: #4f46e5; font-weight: 600; }
        @media (prefers-reduced-motion: reduce) { .reveal { transition-duration: 0.01ms !important; } }
    </style>
</head>
<body class="font-sans bg-stone-50 text-stone-800 antialiased">

    {{-- Header --}}
    <header class="fixed top-0 left-0 right-0 z-50 bg-white/90 backdrop-blur-xl border-b border-stone-200/60">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-16">
            <a href="{{ route('home') }}" class="flex items-center gap-2">
                <span class="text-2xl">🚗</span>
                <span class="font-bold text-xl text-stone-900">RentalMobil</span>
            </a>
            <nav class="hidden md:flex items-center gap-6">
                <a href="/" class="text-sm font-medium text-stone-600 hover:text-brand-600 transition-colors">Beranda</a>
                <a href="/docs" class="text-sm font-semibold text-brand-600">Dokumentasi</a>
                <a href="/contact" class="text-sm font-medium text-stone-600 hover:text-brand-600 transition-colors">Kontak</a>
                <a href="/admin/login" class="px-5 py-2 bg-brand-600 text-white text-sm font-semibold rounded-lg hover:bg-brand-700 transition-all">Masuk Admin</a>
            </nav>
        </div>
    </header>

    {{-- Jump Nav --}}
    <div class="fixed top-16 left-0 right-0 z-40 bg-white border-b border-stone-200 shadow-sm" x-data="{ activeSection: 'demo' }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 overflow-x-auto">
            <div class="flex items-center gap-1 py-2 min-w-max">
                <a href="#demo" @click="activeSection = 'demo'" :class="activeSection === 'demo' ? 'bg-brand-50 text-brand-700' : 'text-stone-600 hover:bg-stone-100'" class="px-4 py-2 rounded-lg text-sm font-medium transition-all whitespace-nowrap">Akun Demo</a>
                <a href="#menu" @click="activeSection = 'menu'" :class="activeSection === 'menu' ? 'bg-brand-50 text-brand-700' : 'text-stone-600 hover:bg-stone-100'" class="px-4 py-2 rounded-lg text-sm font-medium transition-all whitespace-nowrap">Struktur Menu</a>
                <a href="#tutorial" @click="activeSection = 'tutorial'" :class="activeSection === 'tutorial' ? 'bg-brand-50 text-brand-700' : 'text-stone-600 hover:bg-stone-100'" class="px-4 py-2 rounded-lg text-sm font-medium transition-all whitespace-nowrap">Tutorial</a>
                <a href="#fitur" @click="activeSection = 'fitur'" :class="activeSection === 'fitur' ? 'bg-brand-50 text-brand-700' : 'text-stone-600 hover:bg-stone-100'" class="px-4 py-2 rounded-lg text-sm font-medium transition-all whitespace-nowrap">Fitur Lengkap</a>
                <a href="#cta" @click="activeSection = 'cta'" :class="activeSection === 'cta' ? 'bg-brand-50 text-brand-700' : 'text-stone-600 hover:bg-stone-100'" class="px-4 py-2 rounded-lg text-sm font-medium transition-all whitespace-nowrap">Mulai</a>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <main class="pt-32 pb-20">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Title --}}
            <div class="text-center mb-16 reveal">
                <h1 class="text-4xl lg:text-5xl font-bold text-stone-900 mb-4">Dokumentasi RentalMobil</h1>
                <p class="text-lg text-stone-500 max-w-2xl mx-auto">Panduan lengkap menggunakan sistem manajemen rental mobil dari awal hingga mahir.</p>
            </div>

            {{-- Demo Accounts --}}
            <section id="demo" class="mb-20 reveal">
                <div class="flex items-center gap-3 mb-6">
                    <span class="text-2xl">🧪</span>
                    <h2 class="text-2xl font-bold text-stone-900">Akun Demo</h2>
                </div>
                <p class="text-stone-600 mb-6">Gunakan akun berikut untuk mencoba seluruh fitur admin panel. Password untuk semua akun: <code class="bg-stone-100 px-2 py-1 rounded font-mono text-sm">password</code></p>
                <div class="bg-white rounded-2xl shadow-lg border border-stone-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-stone-900 text-white">
                                    <th class="px-6 py-4 text-left font-semibold uppercase tracking-wider text-xs">Role</th>
                                    <th class="px-6 py-4 text-left font-semibold uppercase tracking-wider text-xs">Email</th>
                                    <th class="px-6 py-4 text-left font-semibold uppercase tracking-wider text-xs">Password</th>
                                    <th class="px-6 py-4 text-left font-semibold uppercase tracking-wider text-xs hidden sm:table-cell">Cakupan Akses</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-stone-100">
                                <tr class="hover:bg-stone-50 transition-colors">
                                    <td class="px-6 py-4"><span class="inline-flex items-center gap-1.5 font-semibold text-stone-900"><span class="w-2 h-2 bg-brand-500 rounded-full"></span> Owner</span></td>
                                    <td class="px-6 py-4 font-mono text-xs text-stone-600">admin@rentalmobil.test</td>
                                    <td class="px-6 py-4 font-mono text-xs text-stone-600">password</td>
                                    <td class="px-6 py-4 text-stone-500 hidden sm:table-cell">Semua akses, integrasi, sistem, dan laporan</td>
                                </tr>
                                <tr class="hover:bg-stone-50 transition-colors">
                                    <td class="px-6 py-4"><span class="inline-flex items-center gap-1.5 font-semibold text-stone-900"><span class="w-2 h-2 bg-blue-500 rounded-full"></span> Admin</span></td>
                                    <td class="px-6 py-4 font-mono text-xs text-stone-600">admin2@rentalmobil.test</td>
                                    <td class="px-6 py-4 font-mono text-xs text-stone-600">password</td>
                                    <td class="px-6 py-4 text-stone-500 hidden sm:table-cell">Data master, transaksi, dan operasional</td>
                                </tr>
                                <tr class="hover:bg-stone-50 transition-colors">
                                    <td class="px-6 py-4"><span class="inline-flex items-center gap-1.5 font-semibold text-stone-900"><span class="w-2 h-2 bg-emerald-500 rounded-full"></span> Manager</span></td>
                                    <td class="px-6 py-4 font-mono text-xs text-stone-600">manager@rentalmobil.test</td>
                                    <td class="px-6 py-4 font-mono text-xs text-stone-600">password</td>
                                    <td class="px-6 py-4 text-stone-500 hidden sm:table-cell">Operasional, booking, armada, laporan</td>
                                </tr>
                                <tr class="hover:bg-stone-50 transition-colors">
                                    <td class="px-6 py-4"><span class="inline-flex items-center gap-1.5 font-semibold text-stone-900"><span class="w-2 h-2 bg-amber-500 rounded-full"></span> Kasir</span></td>
                                    <td class="px-6 py-4 font-mono text-xs text-stone-600">kasir@rentalmobil.test</td>
                                    <td class="px-6 py-4 font-mono text-xs text-stone-600">password</td>
                                    <td class="px-6 py-4 text-stone-500 hidden sm:table-cell">Pembayaran, invoice, struk</td>
                                </tr>
                                <tr class="hover:bg-stone-50 transition-colors">
                                    <td class="px-6 py-4"><span class="inline-flex items-center gap-1.5 font-semibold text-stone-900"><span class="w-2 h-2 bg-rose-500 rounded-full"></span> Driver</span></td>
                                    <td class="px-6 py-4 font-mono text-xs text-stone-600">driver@rentalmobil.test</td>
                                    <td class="px-6 py-4 font-mono text-xs text-stone-600">password</td>
                                    <td class="px-6 py-4 text-stone-500 hidden sm:table-cell">Jadwal pickup/dropoff, GPS tracking</td>
                                </tr>
                                <tr class="hover:bg-stone-50 transition-colors">
                                    <td class="px-6 py-4"><span class="inline-flex items-center gap-1.5 font-semibold text-stone-900"><span class="w-2 h-2 bg-cyan-500 rounded-full"></span> Customer</span></td>
                                    <td class="px-6 py-4 font-mono text-xs text-stone-600">customer@rentalmobil.test</td>
                                    <td class="px-6 py-4 font-mono text-xs text-stone-600">password</td>
                                    <td class="px-6 py-4 text-stone-500 hidden sm:table-cell">Portal booking, histori, invoice</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            {{-- Admin Menu Structure --}}
            <section id="menu" class="mb-20 reveal">
                <div class="flex items-center gap-3 mb-6">
                    <span class="text-2xl">📋</span>
                    <h2 class="text-2xl font-bold text-stone-900">Struktur Menu Admin</h2>
                </div>
                <p class="text-stone-600 mb-8">Menu admin diorganisir mengikuti alur bisnis rental mobil. Berikut struktur lengkapnya:</p>

                <div class="grid sm:grid-cols-2 gap-6">
                    {{-- Data Utama --}}
                    <div class="bg-white rounded-xl border border-stone-200 p-6 shadow-sm">
                        <h3 class="font-bold text-stone-900 mb-3 flex items-center gap-2">
                            <span class="w-8 h-8 rounded-lg bg-brand-100 text-brand-600 flex items-center justify-center text-sm">📦</span>
                            Data Utama
                        </h3>
                        <ul class="space-y-2 text-sm text-stone-600">
                            <li class="flex items-center gap-2"><span class="text-stone-400">→</span> Kategori Kendaraan</li>
                            <li class="flex items-center gap-2"><span class="text-stone-400">→</span> Merek & Tipe Kendaraan</li>
                            <li class="flex items-center gap-2"><span class="text-stone-400">→</span> Unit Kendaraan</li>
                            <li class="flex items-center gap-2"><span class="text-stone-400">→</span> Lokasi / Cabang</li>
                            <li class="flex items-center gap-2"><span class="text-stone-400">→</span> Pelanggan & Dokumen</li>
                            <li class="flex items-center gap-2"><span class="text-stone-400">→</span> Driver</li>
                            <li class="flex items-center gap-2"><span class="text-stone-400">→</span> Addon & Layanan</li>
                            <li class="flex items-center gap-2"><span class="text-stone-400">→</span> Metode Bayar</li>
                        </ul>
                    </div>

                    {{-- Reservasi --}}
                    <div class="bg-white rounded-xl border border-stone-200 p-6 shadow-sm">
                        <h3 class="font-bold text-stone-900 mb-3 flex items-center gap-2">
                            <span class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center text-sm">💳</span>
                            Reservasi & Rental
                        </h3>
                        <ul class="space-y-2 text-sm text-stone-600">
                            <li class="flex items-center gap-2"><span class="text-stone-400">→</span> Kuotasi & Reservasi</li>
                            <li class="flex items-center gap-2"><span class="text-stone-400">→</span> Kalender Armada & Daftar Tunggu</li>
                            <li class="flex items-center gap-2"><span class="text-stone-400">→</span> Order Sewa & Kontrak</li>
                            <li class="flex items-center gap-2"><span class="text-stone-400">→</span> Tagihan</li>
                            <li class="flex items-center gap-2"><span class="text-stone-400">→</span> Pembayaran</li>
                            <li class="flex items-center gap-2"><span class="text-stone-400">→</span> Pengembalian</li>
                        </ul>
                    </div>

                    {{-- Logistics --}}
                    <div class="bg-white rounded-xl border border-stone-200 p-6 shadow-sm">
                        <h3 class="font-bold text-stone-900 mb-3 flex items-center gap-2"><span class="w-8 h-8 rounded-lg bg-sky-100 text-sky-600 flex items-center justify-center text-sm">🚚</span>Serah Terima & Logistik</h3>
                        <ul class="space-y-2 text-sm text-stone-600">
                            <li class="flex items-center gap-2"><span class="text-stone-400">→</span> Serah Terima Kendaraan</li>
                            <li class="flex items-center gap-2"><span class="text-stone-400">→</span> Pengiriman & Penjemputan</li>
                            <li class="flex items-center gap-2"><span class="text-stone-400">→</span> Transfer Antar Cabang</li>
                            <li class="flex items-center gap-2"><span class="text-stone-400">→</span> Penilaian Driver</li>
                        </ul>
                    </div>

                    {{-- Finance --}}
                    <div class="bg-white rounded-xl border border-stone-200 p-6 shadow-sm">
                        <h3 class="font-bold text-stone-900 mb-3 flex items-center gap-2">
                            <span class="w-8 h-8 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center text-sm">💰</span>
                            Keuangan
                        </h3>
                        <ul class="space-y-2 text-sm text-stone-600">
                            <li class="flex items-center gap-2"><span class="text-stone-400">→</span> Chart of Accounts (COA)</li>
                            <li class="flex items-center gap-2"><span class="text-stone-400">→</span> Journal Entries</li>
                            <li class="flex items-center gap-2"><span class="text-stone-400">→</span> Expense / Pengeluaran</li>
                            <li class="flex items-center gap-2"><span class="text-stone-400">→</span> Kategori Pengeluaran & Rekening</li>
                        </ul>
                    </div>

                    {{-- Maintenance --}}
                    <div class="bg-white rounded-xl border border-stone-200 p-6 shadow-sm">
                        <h3 class="font-bold text-stone-900 mb-3 flex items-center gap-2">
                            <span class="w-8 h-8 rounded-lg bg-violet-100 text-violet-600 flex items-center justify-center text-sm">🔧</span>
                            Perawatan Armada
                        </h3>
                        <ul class="space-y-2 text-sm text-stone-600">
                            <li class="flex items-center gap-2"><span class="text-stone-400">→</span> Maintenance & Servis</li>
                            <li class="flex items-center gap-2"><span class="text-stone-400">→</span> Bahan Bakar & Kilometer</li>
                            <li class="flex items-center gap-2"><span class="text-stone-400">→</span> Suku Cadang</li>
                            <li class="flex items-center gap-2"><span class="text-stone-400">→</span> Asuransi Kendaraan</li>
                        </ul>
                    </div>

                    {{-- GPS --}}
                    <div class="bg-white rounded-xl border border-stone-200 p-6 shadow-sm">
                        <h3 class="font-bold text-stone-900 mb-3 flex items-center gap-2"><span class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center text-sm">📡</span>GPS & Monitoring</h3>
                        <ul class="space-y-2 text-sm text-stone-600">
                            <li class="flex items-center gap-2"><span class="text-stone-400">→</span> Pusat Kendali</li>
                            <li class="flex items-center gap-2"><span class="text-stone-400">→</span> Peta Armada</li>
                            <li class="flex items-center gap-2"><span class="text-stone-400">→</span> Pelacakan Driver</li>
                            <li class="flex items-center gap-2"><span class="text-stone-400">→</span> Perangkat & Riwayat Posisi</li>
                        </ul>
                    </div>

                    {{-- Security --}}
                    <div class="bg-white rounded-xl border border-stone-200 p-6 shadow-sm">
                        <h3 class="font-bold text-stone-900 mb-3 flex items-center gap-2">
                            <span class="w-8 h-8 rounded-lg bg-rose-100 text-rose-600 flex items-center justify-center text-sm">🛡️</span>
                            Risiko & Keamanan
                        </h3>
                        <ul class="space-y-2 text-sm text-stone-600">
                            <li class="flex items-center gap-2"><span class="text-stone-400">→</span> Blacklist</li>
                            <li class="flex items-center gap-2"><span class="text-stone-400">→</span> Watch List</li>
                            <li class="flex items-center gap-2"><span class="text-stone-400">→</span> Investigation Cases</li>
                            <li class="flex items-center gap-2"><span class="text-stone-400">→</span> Laporan Polisi</li>
                            <li class="flex items-center gap-2"><span class="text-stone-400">→</span> Peringatan & Perintah GPS</li>
                        </ul>
                    </div>

                    {{-- Laporan --}}
                    <div class="bg-white rounded-xl border border-stone-200 p-6 shadow-sm">
                        <h3 class="font-bold text-stone-900 mb-3 flex items-center gap-2">
                            <span class="w-8 h-8 rounded-lg bg-cyan-100 text-cyan-600 flex items-center justify-center text-sm">📊</span>
                            Laporan
                        </h3>
                        <ul class="space-y-2 text-sm text-stone-600">
                            <li class="flex items-center gap-2"><span class="text-stone-400">→</span> Laporan Penjualan</li>
                            <li class="flex items-center gap-2"><span class="text-stone-400">→</span> Laporan Keuangan (P&L)</li>
                            <li class="flex items-center gap-2"><span class="text-stone-400">→</span> Laporan Utilisasi Armada</li>
                            <li class="flex items-center gap-2"><span class="text-stone-400">→</span> Laporan Customer</li>
                        </ul>
                    </div>

                    {{-- Marketing --}}
                    <div class="bg-white rounded-xl border border-stone-200 p-6 shadow-sm">
                        <h3 class="font-bold text-stone-900 mb-3 flex items-center gap-2">
                            <span class="w-8 h-8 rounded-lg bg-pink-100 text-pink-600 flex items-center justify-center text-sm">📢</span>
                            Konten & Marketing
                        </h3>
                        <ul class="space-y-2 text-sm text-stone-600">
                            <li class="flex items-center gap-2"><span class="text-stone-400">→</span> Artikel Blog</li>
                            <li class="flex items-center gap-2"><span class="text-stone-400">→</span> Testimonial</li>
                            <li class="flex items-center gap-2"><span class="text-stone-400">→</span> FAQ</li>
                        </ul>
                    </div>

                    {{-- Sistem --}}
                    <div class="bg-white rounded-xl border border-stone-200 p-6 shadow-sm">
                        <h3 class="font-bold text-stone-900 mb-3 flex items-center gap-2">
                            <span class="w-8 h-8 rounded-lg bg-stone-100 text-stone-600 flex items-center justify-center text-sm">⚙️</span>
                            Sistem & Integrasi
                        </h3>
                        <ul class="space-y-2 text-sm text-stone-600">
                            <li class="flex items-center gap-2"><span class="text-stone-400">→</span> Pengguna & Role</li>
                            <li class="flex items-center gap-2"><span class="text-stone-400">→</span> Provider Dinamis</li>
                            <li class="flex items-center gap-2"><span class="text-stone-400">→</span> Integrasi GPS BYOK</li>
                        </ul>
                    </div>
                </div>
            </section>

            {{-- Tutorial --}}
            <section id="tutorial" class="mb-20 reveal">
                <div class="flex items-center gap-3 mb-6">
                    <span class="text-2xl">📖</span>
                    <h2 class="text-2xl font-bold text-stone-900">Tutorial Langkah demi Langkah</h2>
                </div>
                <p class="text-stone-600 mb-8">Ikuti 8 fase berikut untuk menguasai seluruh fitur RentalMobil sesuai alur bisnis yang sebenarnya.</p>

                <div class="space-y-8">
                    {{-- Phase 1 --}}
                    <div class="bg-white rounded-xl border border-stone-200 p-6 shadow-sm">
                        <div class="flex items-center gap-3 mb-4">
                            <span class="w-10 h-10 rounded-xl bg-brand-600 text-white flex items-center justify-center font-bold">1</span>
                            <h3 class="font-bold text-stone-900 text-lg">Setup Awal</h3>
                        </div>
                        <p class="text-stone-500 text-sm mb-4">Konfigurasi dasar sistem sebelum mulai operasional.</p>
                        <ol class="space-y-2 text-sm text-stone-600">
                            <li class="flex gap-3"><span class="shrink-0 w-6 h-6 rounded-full bg-stone-100 text-stone-600 flex items-center justify-center text-xs font-bold">1</span>Login ke admin panel sebagai Admin</li>
                            <li class="flex gap-3"><span class="shrink-0 w-6 h-6 rounded-full bg-stone-100 text-stone-600 flex items-center justify-center text-xs font-bold">2</span>Buka menu Sistem → System Settings</li>
                            <li class="flex gap-3"><span class="shrink-0 w-6 h-6 rounded-full bg-stone-100 text-stone-600 flex items-center justify-center text-xs font-bold">3</span>Isi informasi perusahaan: nama, alamat, telepon, NPWP</li>
                            <li class="flex gap-3"><span class="shrink-0 w-6 h-6 rounded-full bg-stone-100 text-stone-600 flex items-center justify-center text-xs font-bold">4</span>Setup lokasi / cabang rental</li>
                            <li class="flex gap-3"><span class="shrink-0 w-6 h-6 rounded-full bg-stone-100 text-stone-600 flex items-center justify-center text-xs font-bold">5</span>Setup user admin dan role akses</li>
                        </ol>
                    </div>

                    {{-- Phase 2 --}}
                    <div class="bg-white rounded-xl border border-stone-200 p-6 shadow-sm">
                        <div class="flex items-center gap-3 mb-4">
                            <span class="w-10 h-10 rounded-xl bg-brand-600 text-white flex items-center justify-center font-bold">2</span>
                            <h3 class="font-bold text-stone-900 text-lg">Data Master Kendaraan</h3>
                        </div>
                        <p class="text-stone-500 text-sm mb-4">Input seluruh data armada kendaraan.</p>
                        <ol class="space-y-2 text-sm text-stone-600">
                            <li class="flex gap-3"><span class="shrink-0 w-6 h-6 rounded-full bg-stone-100 text-stone-600 flex items-center justify-center text-xs font-bold">1</span>Tambahkan kategori kendaraan (SUV, Sedan, MPV, ELF, Bus)</li>
                            <li class="flex gap-3"><span class="shrink-0 w-6 h-6 rounded-full bg-stone-100 text-stone-600 flex items-center justify-center text-xs font-bold">2</span>Tambahkan merek & tipe kendaraan</li>
                            <li class="flex gap-3"><span class="shrink-0 w-6 h-6 rounded-full bg-stone-100 text-stone-600 flex items-center justify-center text-xs font-bold">3</span>Input unit kendaraan: plat nomor, tahun, warna, km, foto</li>
                            <li class="flex gap-3"><span class="shrink-0 w-6 h-6 rounded-full bg-stone-100 text-stone-600 flex items-center justify-center text-xs font-bold">4</span>Atur harga rental per kategori (harian, mingguan, bulanan)</li>
                            <li class="flex gap-3"><span class="shrink-0 w-6 h-6 rounded-full bg-stone-100 text-stone-600 flex items-center justify-center text-xs font-bold">5</span>Tambahkan addon: driver, baby seat, GPS, asuransi tambahan</li>
                        </ol>
                    </div>

                    {{-- Phase 3 --}}
                    <div class="bg-white rounded-xl border border-stone-200 p-6 shadow-sm">
                        <div class="flex items-center gap-3 mb-4">
                            <span class="w-10 h-10 rounded-xl bg-brand-600 text-white flex items-center justify-center font-bold">3</span>
                            <h3 class="font-bold text-stone-900 text-lg">Data Master Pelanggan & Driver</h3>
                        </div>
                        <p class="text-stone-500 text-sm mb-4">Input data pelanggan dan driver yang sudah ada.</p>
                        <ol class="space-y-2 text-sm text-stone-600">
                            <li class="flex gap-3"><span class="shrink-0 w-6 h-6 rounded-full bg-stone-100 text-stone-600 flex items-center justify-center text-xs font-bold">1</span>Tambahkan data pelanggan: nama, KTP, SIM, telepon, email</li>
                            <li class="flex gap-3"><span class="shrink-0 w-6 h-6 rounded-full bg-stone-100 text-stone-600 flex items-center justify-center text-xs font-bold">2</span>Upload foto KTP & SIM pelanggan</li>
                            <li class="flex gap-3"><span class="shrink-0 w-6 h-6 rounded-full bg-stone-100 text-stone-600 flex items-center justify-center text-xs font-bold">3</span>Tambahkan data driver: nama, SIM, rating, disponibilitas</li>
                            <li class="flex gap-3"><span class="shrink-0 w-6 h-6 rounded-full bg-stone-100 text-stone-600 flex items-center justify-center text-xs font-bold">4</span>Setup payment methods: tunai, transfer, kartu, e-wallet</li>
                        </ol>
                    </div>

                    {{-- Phase 4 --}}
                    <div class="bg-white rounded-xl border border-stone-200 p-6 shadow-sm">
                        <div class="flex items-center gap-3 mb-4">
                            <span class="w-10 h-10 rounded-xl bg-brand-600 text-white flex items-center justify-center font-bold">4</span>
                            <h3 class="font-bold text-stone-900 text-lg">Transaksi Harian: Booking → Order → Invoice</h3>
                        </div>
                        <p class="text-stone-500 text-sm mb-4">Alur transaksi utama dari awal hingga pembayaran.</p>
                        <ol class="space-y-2 text-sm text-stone-600">
                            <li class="flex gap-3"><span class="shrink-0 w-6 h-6 rounded-full bg-stone-100 text-stone-600 flex items-center justify-center text-xs font-bold">1</span>Buat quotation untuk pelanggan potensial</li>
                            <li class="flex gap-3"><span class="shrink-0 w-6 h-6 rounded-full bg-stone-100 text-stone-600 flex items-center justify-center text-xs font-bold">2</span>Konversi quotation → booking (kendaraan otomatis hold)</li>
                            <li class="flex gap-3"><span class="shrink-0 w-6 h-6 rounded-full bg-stone-100 text-stone-600 flex items-center justify-center text-xs font-bold">3</span>Konfirmasi booking → rental order aktif</li>
                            <li class="flex gap-3"><span class="shrink-0 w-6 h-6 rounded-full bg-stone-100 text-stone-600 flex items-center justify-center text-xs font-bold">4</span>Generate invoice otomatis dari rental order</li>
                            <li class="flex gap-3"><span class="shrink-0 w-6 h-6 rounded-full bg-stone-100 text-stone-600 flex items-center justify-center text-xs font-bold">5</span>Verifikasi pembayaran masuk</li>
                        </ol>
                    </div>

                    {{-- Phase 5 --}}
                    <div class="bg-white rounded-xl border border-stone-200 p-6 shadow-sm">
                        <div class="flex items-center gap-3 mb-4">
                            <span class="w-10 h-10 rounded-xl bg-brand-600 text-white flex items-center justify-center font-bold">5</span>
                            <h3 class="font-bold text-stone-900 text-lg">Operasional: Pengembalian & Maintenance</h3>
                        </div>
                        <p class="text-stone-500 text-sm mb-4">Proses pengembalian kendaraan dan perawatan armada.</p>
                        <ol class="space-y-2 text-sm text-stone-600">
                            <li class="flex gap-3"><span class="shrink-0 w-6 h-6 rounded-full bg-stone-100 text-stone-600 flex items-center justify-center text-xs font-bold">1</span>Proses pengembalian kendaraan + inspeksi kondisi</li>
                            <li class="flex gap-3"><span class="shrink-0 w-6 h-6 rounded-full bg-stone-100 text-stone-600 flex items-center justify-center text-xs font-bold">2</span>Catat KM akhir dan hitung denda keterlambatan</li>
                            <li class="flex gap-3"><span class="shrink-0 w-6 h-6 rounded-full bg-stone-100 text-stone-600 flex items-center justify-center text-xs font-bold">3</span>Buat work order maintenance jika ada kerusakan</li>
                            <li class="flex gap-3"><span class="shrink-0 w-6 h-6 rounded-full bg-stone-100 text-stone-600 flex items-center justify-center text-xs font-bold">4</span>Update status kendaraan: maintenance → available</li>
                            <li class="flex gap-3"><span class="shrink-0 w-6 h-6 rounded-full bg-stone-100 text-stone-600 flex items-center justify-center text-xs font-bold">5</span>Ajukan klaim asuransi jika diperlukan</li>
                        </ol>
                    </div>

                    {{-- Phase 6 --}}
                    <div class="bg-white rounded-xl border border-stone-200 p-6 shadow-sm">
                        <div class="flex items-center gap-3 mb-4">
                            <span class="w-10 h-10 rounded-xl bg-brand-600 text-white flex items-center justify-center font-bold">6</span>
                            <h3 class="font-bold text-stone-900 text-lg">Keamanan & Anti-Fraud</h3>
                        </div>
                        <p class="text-stone-500 text-sm mb-4">Pencegahan dan penanganan risiko bisnis.</p>
                        <ol class="space-y-2 text-sm text-stone-600">
                            <li class="flex gap-3"><span class="shrink-0 w-6 h-6 rounded-full bg-stone-100 text-stone-600 flex items-center justify-center text-xs font-bold">1</span>Identifikasi pelanggan bermasalah → tambahkan ke blacklist</li>
                            <li class="flex gap-3"><span class="shrink-0 w-6 h-6 rounded-full bg-stone-100 text-stone-600 flex items-center justify-center text-xs font-bold">2</span>Monitor pelanggan berisiko via watch list</li>
                            <li class="flex gap-3"><span class="shrink-0 w-6 h-6 rounded-full bg-stone-100 text-stone-600 flex items-center justify-center text-xs font-bold">3</span>Buat investigation case untuk kehilangan/kerusakan</li>
                            <li class="flex gap-3"><span class="shrink-0 w-6 h-6 rounded-full bg-stone-100 text-stone-600 flex items-center justify-center text-xs font-bold">4</span>Generate police report jika diperlukan</li>
                            <li class="flex gap-3"><span class="shrink-0 w-6 h-6 rounded-full bg-stone-100 text-stone-600 flex items-center justify-center text-xs font-bold">5</span>Review audit trail secara berkala</li>
                        </ol>
                    </div>

                    {{-- Phase 7 --}}
                    <div class="bg-white rounded-xl border border-stone-200 p-6 shadow-sm">
                        <div class="flex items-center gap-3 mb-4">
                            <span class="w-10 h-10 rounded-xl bg-brand-600 text-white flex items-center justify-center font-bold">7</span>
                            <h3 class="font-bold text-stone-900 text-lg">Keuangan & Accounting</h3>
                        </div>
                        <p class="text-stone-500 text-sm mb-4">Pencatatan keuangan lengkap dengan double-entry bookkeeping.</p>
                        <ol class="space-y-2 text-sm text-stone-600">
                            <li class="flex gap-3"><span class="shrink-0 w-6 h-6 rounded-full bg-stone-100 text-stone-600 flex items-center justify-center text-xs font-bold">1</span>Setup Chart of Accounts (COA) sesuai kebutuhan</li>
                            <li class="flex gap-3"><span class="shrink-0 w-6 h-6 rounded-full bg-stone-100 text-stone-600 flex items-center justify-center text-xs font-bold">2</span>Review journal entries otomatis dari transaksi</li>
                            <li class="flex gap-3"><span class="shrink-0 w-6 h-6 rounded-full bg-stone-100 text-stone-600 flex items-center justify-center text-xs font-bold">3</span>Catat pengeluaran operasional (bensin, servis, pajak)</li>
                            <li class="flex gap-3"><span class="shrink-0 w-6 h-6 rounded-full bg-stone-100 text-stone-600 flex items-center justify-center text-xs font-bold">4</span>Generate laporan P&L bulanan</li>
                        </ol>
                    </div>

                    {{-- Phase 8 --}}
                    <div class="bg-white rounded-xl border border-stone-200 p-6 shadow-sm">
                        <div class="flex items-center gap-3 mb-4">
                            <span class="w-10 h-10 rounded-xl bg-brand-600 text-white flex items-center justify-center font-bold">8</span>
                            <h3 class="font-bold text-stone-900 text-lg">Laporan & Analisis</h3>
                        </div>
                        <p class="text-stone-500 text-sm mb-4">Pantau kinerja bisnis dengan laporan komprehensif.</p>
                        <ol class="space-y-2 text-sm text-stone-600">
                            <li class="flex gap-3"><span class="shrink-0 w-6 h-6 rounded-full bg-stone-100 text-stone-600 flex items-center justify-center text-xs font-bold">1</span>Dashboard overview — revenue, occupancy, trending</li>
                            <li class="flex gap-3"><span class="shrink-0 w-6 h-6 rounded-full bg-stone-100 text-stone-600 flex items-center justify-center text-xs font-bold">2</span>Laporan penjualan — harian, mingguan, bulanan</li>
                            <li class="flex gap-3"><span class="shrink-0 w-6 h-6 rounded-full bg-stone-100 text-stone-600 flex items-center justify-center text-xs font-bold">3</span>Laporan utilisasi — which cars make money</li>
                            <li class="flex gap-3"><span class="shrink-0 w-6 h-6 rounded-full bg-stone-100 text-stone-600 flex items-center justify-center text-xs font-bold">4</span>Export PDF/Excel untuk presentasi</li>
                            <li class="flex gap-3"><span class="shrink-0 w-6 h-6 rounded-full bg-stone-100 text-stone-600 flex items-center justify-center text-xs font-bold">5</span>Analisis trend dan forecasting</li>
                        </ol>
                    </div>
                </div>
            </section>

            {{-- Features --}}
            <section id="fitur" class="mb-20 reveal">
                <div class="flex items-center gap-3 mb-6">
                    <span class="text-2xl">✨</span>
                    <h2 class="text-2xl font-bold text-stone-900">Fitur Lengkap</h2>
                </div>

                <div class="space-y-6">
                    @foreach([
                        ['icon' => '📋', 'title' => 'Booking Management', 'desc' => 'Sistem booking cerdaas dengan auto-overlap check, hold mechanism, dan quotation builder PDF.', 'items' => ['Auto-overlap check', 'Hold mechanism', 'Quotation builder PDF', 'Multi-lokasi pickup/dropoff', 'Status workflow otomatis']],
                        ['icon' => '🚗', 'title' => 'Fleet Management', 'desc' => 'Kontrol penuh atas armada kendaraan — status real-time, maintenance tracking, dan dokumentasi lengkap.', 'items' => ['Vehicle status real-time', 'Maintenance scheduling', 'KM logs otomatis', 'Dokumen STNK/asuransi', 'Photo gallery kondisi']],
                        ['icon' => '💰', 'title' => 'Financial Management', 'desc' => 'Invoice otomatis, verifikasi pembayaran, dan double-entry bookkeeping terintegrasi.', 'items' => ['Invoice auto-gen', 'Payment verification workflow', 'Journal entries otomatis', 'COA lengkap (P&L, balance sheet)', 'Multi-payment support']],
                        ['icon' => '👥', 'title' => 'Customer Management', 'desc' => 'Kenali pelanggan dengan trust scoring, verifikasi dokumen, dan loyalty tier.', 'items' => ['Trust scoring', 'Document verification (KTP/SIM)', 'Loyalty tiers', 'Rental history lengkap', 'Corporate accounts']],
                        ['icon' => '🛡️', 'title' => 'Security & Anti-Fraud', 'desc' => 'Lindungi bisnis dari risiko dengan blacklist, watch list, dan investigation cases.', 'items' => ['Blacklist pelanggan', 'Watch list monitoring', 'Investigation cases', 'Police report generator', 'Audit trail lengkap']],
                        ['icon' => '📊', 'title' => 'Reports & Analytics', 'desc' => 'Laporan penjualan, keuangan, dan operasional dengan grafik interaktif dan export PDF/Excel.', 'items' => ['Sales reports', 'Financial reports (P&L)', 'Utilization reports', 'Chart interaktif (Chart.js)', 'Export PDF & Excel']],
                    ] as $feature)
                    <div class="bg-white rounded-xl border border-stone-200 p-6 shadow-sm">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-xl bg-brand-50 flex items-center justify-center text-2xl shrink-0">{{ $feature['icon'] }}</div>
                            <div class="flex-1">
                                <h3 class="font-bold text-stone-900 text-lg mb-1">{{ $feature['title'] }}</h3>
                                <p class="text-stone-600 text-sm mb-3">{{ $feature['desc'] }}</p>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($feature['items'] as $item)
                                    <span class="inline-flex items-center gap-1 bg-stone-100 text-stone-700 rounded-full px-3 py-1 text-xs font-medium">
                                        <span class="text-emerald-500">✓</span> {{ $item }}
                                    </span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </section>

            {{-- CTA --}}
            <section id="cta" class="reveal">
                <div class="bg-gradient-to-r from-brand-600 to-indigo-600 rounded-2xl p-8 lg:p-12 text-center text-white shadow-2xl shadow-brand-600/25">
                    <h2 class="text-2xl lg:text-3xl font-bold mb-3">Siap Mencoba?</h2>
                    <p class="text-brand-100 mb-6 max-w-lg mx-auto">Masuk ke admin panel dengan akun demo dan jelajahi seluruh fitur RentalMobil.</p>
                    <div class="flex flex-col sm:flex-row justify-center gap-3">
                        <a href="/admin/login" class="inline-flex items-center justify-center gap-2 bg-white text-brand-700 px-8 py-3 rounded-xl font-bold hover:bg-brand-50 transition-all hover:shadow-lg">
                            Masuk ke Admin Panel →
                        </a>
                        <a href="/" class="inline-flex items-center justify-center gap-2 border-2 border-white/30 text-white px-8 py-3 rounded-xl font-semibold hover:bg-white/10 transition-all">
                            Kembali ke Beranda
                        </a>
                    </div>
                </div>
            </section>
        </div>
    </main>

    {{-- Footer --}}
    <footer class="bg-stone-950 text-stone-400 py-12 border-t border-stone-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row justify-between items-center gap-4">
            <div class="flex items-center gap-2">
                <span class="text-xl">🚗</span>
                <span class="font-bold text-white">RentalMobil</span>
            </div>
            <p class="text-xs">&copy; {{ date('Y') }} RentalMobil. All rights reserved. Powered by Laravel</p>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const reveals = document.querySelectorAll('.reveal');
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) entry.target.classList.add('visible');
                });
            }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });
            reveals.forEach(el => observer.observe(el));
        });
    </script>
</body>
</html>

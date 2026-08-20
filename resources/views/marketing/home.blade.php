<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RentalMobil — Sistem Manajemen Rental Mobil Profesional</title>
    <meta name="description" content="Sistem manajemen rental mobil lengkap. Booking, fleet, keuangan, keamanan — semua terintegrasi dalam satu dashboard.">
    <meta property="og:title" content="RentalMobil — Sistem Manajemen Rental Mobil Profesional">
    <meta property="og:description" content="Kelola seluruh operasional rental mobil dari satu dashboard. Booking, armada, keuangan, dan keamanan.">
    <meta property="og:type" content="website">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
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
        @keyframes fadeSlideUp { 0% { transform: translateY(40px); opacity: 0; } 100% { transform: translateY(0); opacity: 1; } }
        @keyframes floatSlow { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-12px); } }
        @keyframes shimmer { 0% { background-position: -200% 0; } 100% { background-position: 200% 0; } }
        .animate-float-slow { animation: floatSlow 5s ease-in-out infinite; }
        .card-lift { transition: transform .35s cubic-bezier(.16,1,.3,1), box-shadow .35s ease; }
        .card-lift:hover { transform: translateY(-6px); box-shadow: 0 24px 48px -12px rgba(0,0,0,.18); }
        .reveal { opacity: 0; transform: translateY(30px); transition: opacity .7s ease, transform .7s cubic-bezier(.16,1,.3,1); }
        .reveal.visible { opacity: 1; transform: translateY(0); }
        .stagger-1 { transition-delay: .1s; }
        .stagger-2 { transition-delay: .2s; }
        .stagger-3 { transition-delay: .3s; }
        .stagger-4 { transition-delay: .4s; }
        .gradient-hero { background: linear-gradient(135deg, #1e1b4b 0%, #312e81 30%, #4338ca 60%, #6366f1 100%); }
        .gradient-cta { background: linear-gradient(135deg, #1e1b4b 0%, #0f172a 100%); }
        .glass { background: rgba(255,255,255,0.08); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.12); }
        .browser-chrome { background: #f8f9fa; border-radius: 12px 12px 0 0; padding: 10px 14px; display: flex; align-items: center; gap: 8px; }
        .browser-chrome .dot { width: 10px; height: 10px; border-radius: 50%; }
        .browser-chrome .url-bar { flex: 1; background: #fff; border-radius: 6px; padding: 4px 12px; font-size: 11px; color: #6b7280; font-family: monospace; }
        @media (prefers-reduced-motion: reduce) {
            .reveal, .card-lift, .animate-float-slow { animation-duration: 0.01ms !important; transition-duration: 0.01ms !important; }
        }
    </style>
</head>
<body class="font-sans bg-stone-50 text-stone-800 antialiased">

    {{-- Header --}}
    <header class="fixed top-0 left-0 right-0 z-50 transition-all duration-300" x-data="{ mobileOpen: false, scrolled: false }" @scroll.window="scrolled = (window.scrollY > 40)">
        <div :class="scrolled ? 'bg-white/90 backdrop-blur-xl shadow-sm border-b border-stone-200/60' : 'bg-transparent'" class="transition-all duration-300">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-16">
                <a href="{{ route('home') }}" class="flex items-center gap-2">
                    <span class="text-2xl">🚗</span>
                    <span class="font-bold text-xl" :class="scrolled ? 'text-stone-900' : 'text-white'">RentalMobil</span>
                </a>
                <nav class="hidden md:flex items-center gap-8">
                    <a href="#fitur" :class="scrolled ? 'text-stone-600 hover:text-brand-600' : 'text-white/80 hover:text-white'" class="text-sm font-medium transition-colors">Fitur</a>
                    <a href="#demo" :class="scrolled ? 'text-stone-600 hover:text-brand-600' : 'text-white/80 hover:text-white'" class="text-sm font-medium transition-colors">Demo</a>
                    <a href="#harga" :class="scrolled ? 'text-stone-600 hover:text-brand-600' : 'text-white/80 hover:text-white'" class="text-sm font-medium transition-colors">Harga</a>
                    <a href="/docs" :class="scrolled ? 'text-stone-600 hover:text-brand-600' : 'text-white/80 hover:text-white'" class="text-sm font-medium transition-colors">Dokumentasi</a>
                    <a href="/contact" :class="scrolled ? 'text-stone-600 hover:text-brand-600' : 'text-white/80 hover:text-white'" class="text-sm font-medium transition-colors">Hubungi</a>
                    <a href="/admin/login" class="px-5 py-2 bg-brand-600 text-white text-sm font-semibold rounded-lg hover:bg-brand-700 transition-all hover:shadow-lg hover:shadow-brand-600/25">Masuk Admin</a>
                </nav>
                <button class="md:hidden" :class="scrolled ? 'text-stone-900' : 'text-white'" @click="mobileOpen = !mobileOpen">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path x-show="!mobileOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        <path x-show="mobileOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
        <div x-show="mobileOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2" class="md:hidden bg-white border-b border-stone-200 shadow-lg" x-cloak>
            <div class="px-4 py-4 space-y-3">
                <a href="#fitur" @click="mobileOpen = false" class="block text-stone-700 font-medium hover:text-brand-600 transition-colors">Fitur</a>
                <a href="#demo" @click="mobileOpen = false" class="block text-stone-700 font-medium hover:text-brand-600 transition-colors">Demo</a>
                <a href="#harga" @click="mobileOpen = false" class="block text-stone-700 font-medium hover:text-brand-600 transition-colors">Harga</a>
                <a href="/docs" class="block text-stone-700 font-medium hover:text-brand-600 transition-colors">Dokumentasi</a>
                <a href="/contact" class="block text-stone-700 font-medium hover:text-brand-600 transition-colors">Hubungi</a>
                <a href="/admin/login" class="block text-center px-5 py-2.5 bg-brand-600 text-white text-sm font-semibold rounded-lg hover:bg-brand-700 transition-all">Masuk Admin</a>
            </div>
        </div>
    </header>

    {{-- Hero --}}
    <section class="relative min-h-[90vh] gradient-hero overflow-hidden flex items-center">
        <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(circle at 20% 50%, rgba(129,140,248,0.4) 0%, transparent 50%), radial-gradient(circle at 80% 20%, rgba(165,180,252,0.3) 0%, transparent 50%)"></div>
        <div class="absolute -bottom-32 -right-32 text-[20rem] opacity-[0.04] animate-float-slow select-none">🚗</div>
        <div class="absolute top-20 left-10 text-[5rem] opacity-[0.04] animate-float-slow select-none" style="animation-delay: 1.5s">⚙️</div>
        <div class="absolute top-40 right-20 text-[4rem] opacity-[0.04] animate-float-slow select-none" style="animation-delay: 3s">📊</div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-32 lg:py-40">
            <div class="max-w-3xl">
                <div class="reveal visible inline-flex items-center gap-2 glass rounded-full px-4 py-1.5 mb-6">
                    <span class="w-2 h-2 bg-emerald-400 rounded-full animate-pulse"></span>
                    <span class="text-brand-100 text-sm font-medium">Trusted by 200+ rental mobil di Indonesia</span>
                </div>
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-white leading-[1.1] mb-6 reveal stagger-1 visible">
                    Sistem Manajemen<br>
                    <span class="text-brand-200">Rental Mobil Profesional</span>
                </h1>
                <p class="text-lg sm:text-xl text-brand-100/80 leading-relaxed mb-10 max-w-xl reveal stagger-2 visible">
                    Kelola seluruh operasional rental mobil — booking, armada, keuangan, dan keamanan — dari satu dashboard terintegrasi.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 reveal stagger-3 visible">
                    <a href="/admin/login" class="inline-flex items-center justify-center gap-2 px-8 py-4 bg-white text-brand-700 font-bold text-base rounded-xl hover:bg-brand-50 transition-all hover:shadow-xl hover:shadow-black/10 hover:-translate-y-0.5">
                        Coba Demo Gratis
                    </a>
                    <a href="/docs" class="inline-flex items-center justify-center gap-2 px-8 py-4 glass text-white font-semibold text-base rounded-xl hover:bg-white/20 transition-all hover:-translate-y-0.5">
                        Lihat Dokumentasi →
                    </a>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-6 mt-14 reveal stagger-4 visible">
                    <div>
                        <div class="text-3xl font-bold text-white">10+</div>
                        <div class="text-sm text-brand-200/70">Modul Fitur</div>
                    </div>
                    <div>
                        <div class="text-3xl font-bold text-white">50+</div>
                        <div class="text-sm text-brand-200/70">Kendaraan</div>
                    </div>
                    <div>
                        <div class="text-3xl font-bold text-white">3</div>
                        <div class="text-sm text-brand-200/70">Lokasi</div>
                    </div>
                    <div>
                        <div class="text-3xl font-bold text-white">99.9%</div>
                        <div class="text-sm text-brand-200/70">Uptime</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="absolute bottom-0 left-0 right-0 h-24 bg-gradient-to-t from-stone-50 to-transparent"></div>
    </section>

    {{-- Trust Strip --}}
    <section class="py-12 bg-white border-b border-stone-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <p class="text-center text-sm text-stone-400 font-medium mb-8 uppercase tracking-wider">Cocok untuk</p>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-6">
                <div class="flex flex-col items-center gap-3 card-lift cursor-default">
                    <div class="w-16 h-16 rounded-2xl bg-brand-50 flex items-center justify-center text-3xl">🏢</div>
                    <span class="text-sm font-semibold text-stone-700">Rental Owner</span>
                </div>
                <div class="flex flex-col items-center gap-3 card-lift cursor-default">
                    <div class="w-16 h-16 rounded-2xl bg-emerald-50 flex items-center justify-center text-3xl">🚛</div>
                    <span class="text-sm font-semibold text-stone-700">Fleet Manager</span>
                </div>
                <div class="flex flex-col items-center gap-3 card-lift cursor-default">
                    <div class="w-16 h-16 rounded-2xl bg-amber-50 flex items-center justify-center text-3xl">💻</div>
                    <span class="text-sm font-semibold text-stone-700">Admin Operasional</span>
                </div>
                <div class="flex flex-col items-center gap-3 card-lift cursor-default">
                    <div class="w-16 h-16 rounded-2xl bg-violet-50 flex items-center justify-center text-3xl">💰</div>
                    <span class="text-sm font-semibold text-stone-700">Kasir</span>
                </div>
                <div class="flex flex-col items-center gap-3 card-lift cursor-default">
                    <div class="w-16 h-16 rounded-2xl bg-rose-50 flex items-center justify-center text-3xl">🤝</div>
                    <span class="text-sm font-semibold text-stone-700">Sales & Marketing</span>
                </div>
            </div>
        </div>
    </section>

    {{-- Problem / Solution --}}
    <section class="py-20 lg:py-28">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl lg:text-4xl font-bold text-stone-900 mb-4 reveal">Masalah yang Sering Dihadapi</h2>
                <p class="text-lg text-stone-500 max-w-2xl mx-auto reveal stagger-1">Kami paham frustrasi mengelola rental mobil secara manual. Inilah solusi kami.</p>
            </div>
            <div class="grid md:grid-cols-3 gap-8">
                <div class="reveal stagger-1">
                    <div class="bg-red-50 border border-red-100 rounded-2xl p-6 mb-4">
                        <div class="text-red-500 text-sm font-semibold mb-2">❌ Masalah</div>
                        <p class="text-stone-700 font-medium">Kelola data booking pakai Excel — rapih awalnya, berantakan setelah ribuan transaksi masuk</p>
                    </div>
                    <div class="bg-emerald-50 border border-emerald-100 rounded-2xl p-6">
                        <div class="text-emerald-600 text-sm font-semibold mb-2">✅ Solusi</div>
                        <p class="text-stone-700 font-medium">Satu dashboard terintegrasi — semua data booking, jadwal, dan status terpusat dalam satu tampilan</p>
                    </div>
                </div>
                <div class="reveal stagger-2">
                    <div class="bg-red-50 border border-red-100 rounded-2xl p-6 mb-4">
                        <div class="text-red-500 text-sm font-semibold mb-2">❌ Masalah</div>
                        <p class="text-stone-700 font-medium">Sulit tracking kendaraan — tidak tahu mobil mana yang sudah dikembalikan, mana yang masih jalan</p>
                    </div>
                    <div class="bg-emerald-50 border border-emerald-100 rounded-2xl p-6">
                        <div class="text-emerald-600 text-sm font-semibold mb-2">✅ Solusi</div>
                        <p class="text-stone-700 font-medium">Status kendaraan real-time + GPS tracking — tahu persis lokasi dan kondisi setiap unit armada</p>
                    </div>
                </div>
                <div class="reveal stagger-3">
                    <div class="bg-red-50 border border-red-100 rounded-2xl p-6 mb-4">
                        <div class="text-red-500 text-sm font-semibold mb-2">❌ Masalah</div>
                        <p class="text-stone-700 font-medium">Denda keterlambatan sering terlewat — pelanggan tidak dikenai, arus kas jebol</p>
                    </div>
                    <div class="bg-emerald-50 border border-emerald-100 rounded-2xl p-6">
                        <div class="text-emerald-600 text-sm font-semibold mb-2">✅ Solusi</div>
                        <p class="text-stone-700 font-medium">Auto kalkulasi denda + notifikasi otomatis ke pelanggan — tidak ada yang terlewat</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Features --}}
    <section id="fitur" class="py-20 lg:py-28 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-20">
                <h2 class="text-3xl lg:text-4xl font-bold text-stone-900 mb-4 reveal">Fitur Unggulan</h2>
                <p class="text-lg text-stone-500 max-w-2xl mx-auto reveal stagger-1">Semua yang Anda butuhkan untuk mengelola bisnis rental mobil secara profesional</p>
            </div>

            {{-- Feature 1: Booking Management --}}
            <div class="flex flex-col lg:flex-row items-center gap-12 lg:gap-20 mb-24 reveal">
                <div class="w-full lg:w-1/2">
                    <div class="relative rounded-2xl overflow-hidden shadow-2xl">
                        <div class="browser-chrome">
                            <div class="dot bg-red-400"></div>
                            <div class="dot bg-amber-400"></div>
                            <div class="dot bg-emerald-400"></div>
                            <div class="url-bar">rentalmobil.id/admin/bookings</div>
                        </div>
                        <x-marketing-screen file="bookings.png" label="Booking Management" icon="📋" />
                    </div>
                </div>
                <div class="w-full lg:w-1/2">
                    <div class="inline-flex items-center gap-2 bg-brand-50 text-brand-700 rounded-full px-4 py-1.5 text-sm font-semibold mb-4">Booking Management</div>
                    <h3 class="text-2xl lg:text-3xl font-bold text-stone-900 mb-4">Kelola Booking Tanpa Ribet</h3>
                    <p class="text-stone-600 text-lg leading-relaxed mb-6">Sistem booking otomatis mengecek ketersediaan kendaraan, mencegah overlap jadwal, dan membangun quotation dalam hitungan detik.</p>
                    <ul class="space-y-3">
                        <li class="flex items-center gap-3"><span class="w-6 h-6 rounded-full bg-brand-100 text-brand-600 flex items-center justify-center text-sm">✓</span><span class="text-stone-700">Auto-overlap check — tidak ada double booking</span></li>
                        <li class="flex items-center gap-3"><span class="w-6 h-6 rounded-full bg-brand-100 text-brand-600 flex items-center justify-center text-sm">✓</span><span class="text-stone-700">Hold mechanism — kendaraan ditahan sementara saat proses</span></li>
                        <li class="flex items-center gap-3"><span class="w-6 h-6 rounded-full bg-brand-100 text-brand-600 flex items-center justify-center text-sm">✓</span><span class="text-stone-700">Quotation builder — PDF profesional untuk pelanggan</span></li>
                        <li class="flex items-center gap-3"><span class="w-6 h-6 rounded-full bg-brand-100 text-brand-600 flex items-center justify-center text-sm">✓</span><span class="text-stone-700">Status workflow: draft → confirmed → active → completed</span></li>
                        <li class="flex items-center gap-3"><span class="w-6 h-6 rounded-full bg-brand-100 text-brand-600 flex items-center justify-center text-sm">✓</span><span class="text-stone-700">Multi-lokasi — pickup & dropoff di lokasi berbeda</span></li>
                    </ul>
                </div>
            </div>

            {{-- Feature 2: Fleet Management --}}
            <div class="flex flex-col lg:flex-row-reverse items-center gap-12 lg:gap-20 mb-24 reveal">
                <div class="w-full lg:w-1/2">
                    <div class="relative rounded-2xl overflow-hidden shadow-2xl">
                        <div class="browser-chrome">
                            <div class="dot bg-red-400"></div>
                            <div class="dot bg-amber-400"></div>
                            <div class="dot bg-emerald-400"></div>
                            <div class="url-bar">rentalmobil.id/admin/vehicles</div>
                        </div>
                        <x-marketing-screen file="vehicles.png" label="Fleet Management" icon="🚗" />
                    </div>
                </div>
                <div class="w-full lg:w-1/2">
                    <div class="inline-flex items-center gap-2 bg-emerald-50 text-emerald-700 rounded-full px-4 py-1.5 text-sm font-semibold mb-4">Fleet Management</div>
                    <h3 class="text-2xl lg:text-3xl font-bold text-stone-900 mb-4">Pantau Armada Secara Real-time</h3>
                    <p class="text-stone-600 text-lg leading-relaxed mb-6">Kontrol penuh atas seluruh armada kendaraan. Status, kondisi, dan riwayat pemeliharaan tercatat otomatis.</p>
                    <ul class="space-y-3">
                        <li class="flex items-center gap-3"><span class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-sm">✓</span><span class="text-stone-700">Vehicle status: available, rented, maintenance, retired</span></li>
                        <li class="flex items-center gap-3"><span class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-sm">✓</span><span class="text-stone-700">Maintenance tracking — jadwal servis & riwayat perbaikan</span></li>
                        <li class="flex items-center gap-3"><span class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-sm">✓</span><span class="text-stone-700">KM logs — catatan kilometer tiap pengembalian</span></li>
                        <li class="flex items-center gap-3"><span class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-sm">✓</span><span class="text-stone-700">Dokumen kendaraan — STNK, asuransi, SIM A/B</span></li>
                        <li class="flex items-center gap-3"><span class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-sm">✓</span><span class="text-stone-700">Photo gallery — dokumentasi kondisi kendaraan</span></li>
                    </ul>
                </div>
            </div>

            {{-- Feature 3: Financial --}}
            <div class="flex flex-col lg:flex-row items-center gap-12 lg:gap-20 mb-24 reveal">
                <div class="w-full lg:w-1/2">
                    <div class="relative rounded-2xl overflow-hidden shadow-2xl">
                        <div class="browser-chrome">
                            <div class="dot bg-red-400"></div>
                            <div class="dot bg-amber-400"></div>
                            <div class="dot bg-emerald-400"></div>
                            <div class="url-bar">rentalmobil.id/admin/invoices</div>
                        </div>
                        <x-marketing-screen file="invoices.png" label="Financial Management" icon="💰" />
                    </div>
                </div>
                <div class="w-full lg:w-1/2">
                    <div class="inline-flex items-center gap-2 bg-amber-50 text-amber-700 rounded-full px-4 py-1.5 text-sm font-semibold mb-4">Financial Management</div>
                    <h3 class="text-2xl lg:text-3xl font-bold text-stone-900 mb-4">Keuangan Transparan & Akurat</h3>
                    <p class="text-stone-600 text-lg leading-relaxed mb-6">Invoice otomatis, verifikasi pembayaran, dan pencatatan jurnal keuangan — semua terintegrasi dalam satu sistem.</p>
                    <ul class="space-y-3">
                        <li class="flex items-center gap-3"><span class="w-6 h-6 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center text-sm">✓</span><span class="text-stone-700">Invoice auto-gen dari booking — termasuk denda & addon</span></li>
                        <li class="flex items-center gap-3"><span class="w-6 h-6 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center text-sm">✓</span><span class="text-stone-700">Payment verification — upload bukti transfer, approval workflow</span></li>
                        <li class="flex items-center gap-3"><span class="w-6 h-6 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center text-sm">✓</span><span class="text-stone-700">Journal entries otomatis — double-entry bookkeeping</span></li>
                        <li class="flex items-center gap-3"><span class="w-6 h-6 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center text-sm">✓</span><span class="text-stone-700">COA (Chart of Accounts) lengkap — P&L, balance sheet</span></li>
                        <li class="flex items-center gap-3"><span class="w-6 h-6 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center text-sm">✓</span><span class="text-stone-700">Multi-payment: tunai, transfer, kartu kredit, e-wallet</span></li>
                    </ul>
                </div>
            </div>

            {{-- Feature 4: Customer --}}
            <div class="flex flex-col lg:flex-row-reverse items-center gap-12 lg:gap-20 mb-24 reveal">
                <div class="w-full lg:w-1/2">
                    <div class="relative rounded-2xl overflow-hidden shadow-2xl">
                        <div class="browser-chrome">
                            <div class="dot bg-red-400"></div>
                            <div class="dot bg-amber-400"></div>
                            <div class="dot bg-emerald-400"></div>
                            <div class="url-bar">rentalmobil.id/admin/customers</div>
                        </div>
                        <x-marketing-screen file="customers.png" label="Customer Management" icon="👥" />
                    </div>
                </div>
                <div class="w-full lg:w-1/2">
                    <div class="inline-flex items-center gap-2 bg-violet-50 text-violet-700 rounded-full px-4 py-1.5 text-sm font-semibold mb-4">Customer Management</div>
                    <h3 class="text-2xl lg:text-3xl font-bold text-stone-900 mb-4">Kenali Pelanggan Anda Lebih Dalam</h3>
                    <p class="text-stone-600 text-lg leading-relaxed mb-6">Trust scoring, verifikasi dokumen, dan loyalty tier — bantu Anda memberikan layanan terbaik sekaligus meminimalkan risiko.</p>
                    <ul class="space-y-3">
                        <li class="flex items-center gap-3"><span class="w-6 h-6 rounded-full bg-violet-100 text-violet-600 flex items-center justify-center text-sm">✓</span><span class="text-stone-700">Trust scoring — rating risiko berdasarkan histori</span></li>
                        <li class="flex items-center gap-3"><span class="w-6 h-6 rounded-full bg-violet-100 text-violet-600 flex items-center justify-center text-sm">✓</span><span class="text-stone-700">Document verification — KTP, SIM, NPWP</span></li>
                        <li class="flex items-center gap-3"><span class="w-6 h-6 rounded-full bg-violet-100 text-violet-600 flex items-center justify-center text-sm">✓</span><span class="text-stone-700">Loyalty tiers — bronze, silver, gold, platinum</span></li>
                        <li class="flex items-center gap-3"><span class="w-6 h-6 rounded-full bg-violet-100 text-violet-600 flex items-center justify-center text-sm">✓</span><span class="text-stone-700">Rental history — lengkap dari pertama kali sewa</span></li>
                        <li class="flex items-center gap-3"><span class="w-6 h-6 rounded-full bg-violet-100 text-violet-600 flex items-center justify-center text-sm">✓</span><span class="text-stone-700">Corporate accounts — billing terpisah per perusahaan</span></li>
                    </ul>
                </div>
            </div>

            {{-- Feature 5: Security --}}
            <div class="flex flex-col lg:flex-row items-center gap-12 lg:gap-20 mb-24 reveal">
                <div class="w-full lg:w-1/2">
                    <div class="relative rounded-2xl overflow-hidden shadow-2xl">
                        <div class="browser-chrome">
                            <div class="dot bg-red-400"></div>
                            <div class="dot bg-amber-400"></div>
                            <div class="dot bg-emerald-400"></div>
                            <div class="url-bar">rentalmobil.id/admin/security</div>
                        </div>
                        <x-marketing-screen file="gps-alerts.png" label="Security & Anti-Fraud" icon="🛡️" />
                    </div>
                </div>
                <div class="w-full lg:w-1/2">
                    <div class="inline-flex items-center gap-2 bg-rose-50 text-rose-700 rounded-full px-4 py-1.5 text-sm font-semibold mb-4">Security & Anti-Fraud</div>
                    <h3 class="text-2xl lg:text-3xl font-bold text-stone-900 mb-4">Lindungi Bisnis dari Risiko</h3>
                    <p class="text-stone-600 text-lg leading-relaxed mb-6">Blacklist pelanggan bermasalah, watch list untuk monitoring, dan investigation case untuk kasus kehilangan atau kerusakan.</p>
                    <ul class="space-y-3">
                        <li class="flex items-center gap-3"><span class="w-6 h-6 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center text-sm">✓</span><span class="text-stone-700">Blacklist — blokir pelanggan bermasalah</span></li>
                        <li class="flex items-center gap-3"><span class="w-6 h-6 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center text-sm">✓</span><span class="text-stone-700">Watch list — monitor pelanggan berisiko tinggi</span></li>
                        <li class="flex items-center gap-3"><span class="w-6 h-6 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center text-sm">✓</span><span class="text-stone-700">Investigation cases — lacak kasus kehilangan/kerusakan</span></li>
                        <li class="flex items-center gap-3"><span class="w-6 h-6 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center text-sm">✓</span><span class="text-stone-700">Police reports — generate laporan polisi otomatis</span></li>
                        <li class="flex items-center gap-3"><span class="w-6 h-6 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center text-sm">✓</span><span class="text-stone-700">Audit trail — semua aktivitas tercatat</span></li>
                    </ul>
                </div>
            </div>

            {{-- Feature 6: Reports --}}
            <div class="flex flex-col lg:flex-row-reverse items-center gap-12 lg:gap-20 mb-24 reveal">
                <div class="w-full lg:w-1/2">
                    <div class="relative rounded-2xl overflow-hidden shadow-2xl">
                        <div class="browser-chrome">
                            <div class="dot bg-red-400"></div>
                            <div class="dot bg-amber-400"></div>
                            <div class="dot bg-emerald-400"></div>
                            <div class="url-bar">rentalmobil.id/admin/reports</div>
                        </div>
                        <x-marketing-screen file="operations-report.png" label="Reports & Analytics" icon="📊" />
                    </div>
                </div>
                <div class="w-full lg:w-1/2">
                    <div class="inline-flex items-center gap-2 bg-cyan-50 text-cyan-700 rounded-full px-4 py-1.5 text-sm font-semibold mb-4">Reports & Analytics</div>
                    <h3 class="text-2xl lg:text-3xl font-bold text-stone-900 mb-4">Insight Bisnis Real-time</h3>
                    <p class="text-stone-600 text-lg leading-relaxed mb-6">Laporan penjualan, keuangan, dan operasional dengan grafik interaktif. Export ke PDF/Excel untuk presentasi.</p>
                    <ul class="space-y-3">
                        <li class="flex items-center gap-3"><span class="w-6 h-6 rounded-full bg-cyan-100 text-cyan-600 flex items-center justify-center text-sm">✓</span><span class="text-stone-700">Sales reports — revenue, occupancy rate, top vehicles</span></li>
                        <li class="flex items-center gap-3"><span class="w-6 h-6 rounded-full bg-cyan-100 text-cyan-600 flex items-center justify-center text-sm">✓</span><span class="text-stone-700">Financial reports — P&L, AR/AP aging, cash flow</span></li>
                        <li class="flex items-center gap-3"><span class="w-6 h-6 rounded-full bg-cyan-100 text-cyan-600 flex items-center justify-center text-sm">✓</span><span class="text-stone-700">Operational reports — utilization, damage rate, turnover</span></li>
                        <li class="flex items-center gap-3"><span class="w-6 h-6 rounded-full bg-cyan-100 text-cyan-600 flex items-center justify-center text-sm">✓</span><span class="text-stone-700">Chart interaktif — bar, line, doughnut via Chart.js</span></li>
                        <li class="flex items-center gap-3"><span class="w-6 h-6 rounded-full bg-cyan-100 text-cyan-600 flex items-center justify-center text-sm">✓</span><span class="text-stone-700">Export PDF & Excel — siap presentasi ke investor</span></li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    {{-- Screenshot Gallery --}}
    <section id="galeri" class="py-20 lg:py-28">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl lg:text-4xl font-bold text-stone-900 mb-4 reveal">Tampilan Aplikasi</h2>
                <p class="text-lg text-stone-500 max-w-2xl mx-auto reveal stagger-1">Jelajahi antarmuka admin panel yang dirancang untuk produktivitas</p>
            </div>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach([
                    ['icon' => '🏠', 'title' => 'Dashboard', 'file' => 'dashboard.png'],
                    ['icon' => '📋', 'title' => 'Manajemen Booking', 'file' => 'bookings.png'],
                    ['icon' => '🚗', 'title' => 'Fleet / Armada', 'file' => 'vehicles.png'],
                    ['icon' => '👥', 'title' => 'Pelanggan', 'file' => 'customers.png'],
                    ['icon' => '💰', 'title' => 'Invoice & Pembayaran', 'file' => 'invoices.png'],
                    ['icon' => '📊', 'title' => 'Laporan', 'file' => 'operations-report.png'],
                ] as $screen)
                <div class="group card-lift reveal">
                    <div class="relative rounded-2xl overflow-hidden shadow-lg">
                        <div class="browser-chrome">
                            <div class="dot bg-red-400"></div>
                            <div class="dot bg-amber-400"></div>
                            <div class="dot bg-emerald-400"></div>
                            <div class="url-bar">rentalmobil.id/admin/...</div>
                        </div>
                        <x-marketing-screen :file="$screen['file']" :label="$screen['title']" :icon="$screen['icon']" class="aspect-[16/10]" />
                    </div>
                    <p class="mt-3 text-sm font-medium text-stone-600 text-center">{{ $screen['title'] }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Use Cases --}}
    <section class="py-20 lg:py-28 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl lg:text-4xl font-bold text-stone-900 mb-4 reveal">Siapa yang Menggunakan?</h2>
                <p class="text-lg text-stone-500 max-w-2xl mx-auto reveal stagger-1">Dirancang untuk setiap peran dalam bisnis rental mobil</p>
            </div>
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-8">
                <div class="bg-stone-50 border border-stone-200 rounded-2xl p-8 card-lift reveal stagger-1">
                    <div class="w-14 h-14 rounded-2xl bg-brand-100 flex items-center justify-center text-3xl mb-5">🏢</div>
                    <h3 class="font-bold text-stone-900 text-lg mb-2">Rental Owner</h3>
                    <p class="text-stone-600 text-sm leading-relaxed">Pantau profit, armada, dan tim dalam satu tampilan. Keputusan bisnis berdasarkan data, bukan insting.</p>
                </div>
                <div class="bg-stone-50 border border-stone-200 rounded-2xl p-8 card-lift reveal stagger-2">
                    <div class="w-14 h-14 rounded-2xl bg-emerald-100 flex items-center justify-center text-3xl mb-5">🚛</div>
                    <h3 class="font-bold text-stone-900 text-lg mb-2">Fleet Manager</h3>
                    <p class="text-stone-600 text-sm leading-relaxed">Jadwal servis, kondisi kendaraan, dan distribusi armada — semuanya terpantau secara real-time.</p>
                </div>
                <div class="bg-stone-50 border border-stone-200 rounded-2xl p-8 card-lift reveal stagger-3">
                    <div class="w-14 h-14 rounded-2xl bg-amber-100 flex items-center justify-center text-3xl mb-5">💻</div>
                    <h3 class="font-bold text-stone-900 text-lg mb-2">Admin & Kasir</h3>
                    <p class="text-stone-600 text-sm leading-relaxed">Proses booking, verifikasi pembayaran, cetak invoice — semuanya dalam hitungan detik.</p>
                </div>
                <div class="bg-stone-50 border border-stone-200 rounded-2xl p-8 card-lift reveal stagger-4">
                    <div class="w-14 h-14 rounded-2xl bg-rose-100 flex items-center justify-center text-3xl mb-5">🤝</div>
                    <h3 class="font-bold text-stone-900 text-lg mb-2">Sales & Marketing</h3>
                    <p class="text-stone-600 text-sm leading-relaxed">Pelanggan potensial, quotation, dan follow-up — pipeline penjualan yang terstruktur.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Demo Accounts --}}
    <section id="demo" class="py-20 lg:py-28">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-stone-900 mb-4 reveal">Akun Demo</h2>
                <p class="text-lg text-stone-500 reveal stagger-1">Coba langsung admin panel dengan akun demo berikut</p>
            </div>
            <div class="bg-white rounded-2xl shadow-xl border border-stone-200 overflow-hidden reveal stagger-2">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-stone-900 text-white">
                                <th class="px-6 py-4 text-left font-semibold uppercase tracking-wider text-xs">Role</th>
                                <th class="px-6 py-4 text-left font-semibold uppercase tracking-wider text-xs">Email</th>
                                <th class="px-6 py-4 text-left font-semibold uppercase tracking-wider text-xs">Password</th>
                                <th class="px-6 py-4 text-left font-semibold uppercase tracking-wider text-xs hidden sm:table-cell">Cakupan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100">
                            <tr class="hover:bg-stone-50 transition-colors">
                                <td class="px-6 py-4"><span class="inline-flex items-center gap-1.5 font-semibold text-stone-900"><span class="w-2 h-2 bg-brand-500 rounded-full"></span> Owner</span></td>
                                <td class="px-6 py-4 font-mono text-stone-600">admin@rentalmobil.test</td>
                                <td class="px-6 py-4 font-mono text-stone-600">password</td>
                                <td class="px-6 py-4 text-stone-500 hidden sm:table-cell">Semua akses, pengaturan sistem, laporan keuangan</td>
                            </tr>
                            <tr class="hover:bg-stone-50 transition-colors">
                                <td class="px-6 py-4"><span class="inline-flex items-center gap-1.5 font-semibold text-stone-900"><span class="w-2 h-2 bg-emerald-500 rounded-full"></span> Manager</span></td>
                                <td class="px-6 py-4 font-mono text-stone-600">manager@rentalmobil.test</td>
                                <td class="px-6 py-4 font-mono text-stone-600">password</td>
                                <td class="px-6 py-4 text-stone-500 hidden sm:table-cell">Operasional, booking, armada, laporan</td>
                            </tr>
                            <tr class="hover:bg-stone-50 transition-colors">
                                <td class="px-6 py-4"><span class="inline-flex items-center gap-1.5 font-semibold text-stone-900"><span class="w-2 h-2 bg-amber-500 rounded-full"></span> Admin</span></td>
                                <td class="px-6 py-4 font-mono text-stone-600">admin2@rentalmobil.test</td>
                                <td class="px-6 py-4 font-mono text-stone-600">password</td>
                                <td class="px-6 py-4 text-stone-500 hidden sm:table-cell">Data master, booking, pelanggan</td>
                            </tr>
                            <tr class="hover:bg-stone-50 transition-colors">
                                <td class="px-6 py-4"><span class="inline-flex items-center gap-1.5 font-semibold text-stone-900"><span class="w-2 h-2 bg-violet-500 rounded-full"></span> Kasir</span></td>
                                <td class="px-6 py-4 font-mono text-stone-600">kasir@rentalmobil.test</td>
                                <td class="px-6 py-4 font-mono text-stone-600">password</td>
                                <td class="px-6 py-4 text-stone-500 hidden sm:table-cell">Pembayaran, invoice, struk</td>
                            </tr>
                            <tr class="hover:bg-stone-50 transition-colors">
                                <td class="px-6 py-4"><span class="inline-flex items-center gap-1.5 font-semibold text-stone-900"><span class="w-2 h-2 bg-rose-500 rounded-full"></span> Driver</span></td>
                                <td class="px-6 py-4 font-mono text-stone-600">driver@rentalmobil.test</td>
                                <td class="px-6 py-4 font-mono text-stone-600">password</td>
                                <td class="px-6 py-4 text-stone-500 hidden sm:table-cell">Jadwal pickup/dropoff, GPS tracking</td>
                            </tr>
                            <tr class="hover:bg-stone-50 transition-colors">
                                <td class="px-6 py-4"><span class="inline-flex items-center gap-1.5 font-semibold text-stone-900"><span class="w-2 h-2 bg-cyan-500 rounded-full"></span> Customer</span></td>
                                <td class="px-6 py-4 font-mono text-stone-600">customer@rentalmobil.test</td>
                                <td class="px-6 py-4 font-mono text-stone-600">password</td>
                                <td class="px-6 py-4 text-stone-500 hidden sm:table-cell">Portal booking, histori, invoice</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="text-center mt-6">
                <a href="/admin/login" class="inline-flex items-center gap-2 px-6 py-3 bg-brand-600 text-white font-semibold rounded-xl hover:bg-brand-700 transition-all hover:shadow-lg hover:shadow-brand-600/25 hover:-translate-y-0.5">
                    Masuk ke Admin Panel →
                </a>
            </div>
        </div>
    </section>

    {{-- Pricing --}}
    <section id="harga" class="py-20 lg:py-28 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl lg:text-4xl font-bold text-stone-900 mb-4 reveal">Paket Harga</h2>
                <p class="text-lg text-stone-500 max-w-2xl mx-auto reveal stagger-1">Pilih paket yang sesuai dengan skala bisnis rental Anda</p>
            </div>
            <div class="grid md:grid-cols-3 gap-8 max-w-5xl mx-auto">
                {{-- Starter --}}
                <div class="bg-stone-50 border border-stone-200 rounded-2xl p-8 card-lift reveal stagger-1">
                    <div class="text-sm font-semibold text-stone-500 uppercase tracking-wider mb-2">Starter</div>
                    <div class="flex items-baseline gap-1 mb-6">
                        <span class="text-4xl font-bold text-stone-900">Rp 299rb</span>
                        <span class="text-stone-500">/bulan</span>
                    </div>
                    <p class="text-stone-500 text-sm mb-6">Cocok untuk rental mobil kecil (1-10 unit)</p>
                    <ul class="space-y-3 mb-8 text-sm text-stone-600">
                        <li class="flex items-center gap-2"><span class="text-emerald-500">✓</span> 3 admin user</li>
                        <li class="flex items-center gap-2"><span class="text-emerald-500">✓</span> 20 booking/bulan</li>
                        <li class="flex items-center gap-2"><span class="text-emerald-500">✓</span> Fleet management dasar</li>
                        <li class="flex items-center gap-2"><span class="text-emerald-500">✓</span> Invoice otomatis</li>
                        <li class="flex items-center gap-2"><span class="text-emerald-500">✓</span> Laporan penjualan</li>
                        <li class="flex items-center gap-2 text-stone-400"><span>✕</span> GPS tracking</li>
                        <li class="flex items-center gap-2 text-stone-400"><span>✕</span> Financial accounting</li>
                    </ul>
                    <a href="/admin/login" class="block w-full text-center py-3 rounded-xl border-2 border-stone-300 font-semibold text-stone-700 hover:border-stone-400 hover:bg-stone-100 transition-all">Mulai Gratis</a>
                </div>
                {{-- Growth --}}
                <div class="bg-brand-600 text-white rounded-2xl p-8 card-lift shadow-2xl shadow-brand-600/25 relative reveal stagger-2 md:scale-105">
                    <div class="absolute -top-4 left-1/2 -translate-x-1/2 bg-amber-400 text-amber-900 text-xs font-bold px-4 py-1 rounded-full uppercase tracking-wider">Populer</div>
                    <div class="text-sm font-semibold text-brand-200 uppercase tracking-wider mb-2">Growth</div>
                    <div class="flex items-baseline gap-1 mb-6">
                        <span class="text-4xl font-bold text-white">Rp 599rb</span>
                        <span class="text-brand-200">/bulan</span>
                    </div>
                    <p class="text-brand-200 text-sm mb-6">Untuk rental mobil menengah (10-50 unit)</p>
                    <ul class="space-y-3 mb-8 text-sm text-brand-100">
                        <li class="flex items-center gap-2"><span class="text-emerald-300">✓</span> 10 admin user</li>
                        <li class="flex items-center gap-2"><span class="text-emerald-300">✓</span> Unlimited booking</li>
                        <li class="flex items-center gap-2"><span class="text-emerald-300">✓</span> GPS tracking real-time</li>
                        <li class="flex items-center gap-2"><span class="text-emerald-300">✓</span> Financial accounting lengkap</li>
                        <li class="flex items-center gap-2"><span class="text-emerald-300">✓</span> Security & anti-fraud</li>
                        <li class="flex items-center gap-2"><span class="text-emerald-300">✓</span> Portal pelanggan</li>
                        <li class="flex items-center gap-2"><span class="text-emerald-300">✓</span> Laporan analitik lengkap</li>
                    </ul>
                    <a href="/admin/login" class="block w-full text-center py-3 rounded-xl bg-white text-brand-700 font-bold hover:bg-brand-50 transition-all hover:shadow-lg">Pilih Growth</a>
                </div>
                {{-- Enterprise --}}
                <div class="bg-stone-900 text-white rounded-2xl p-8 card-lift reveal stagger-3">
                    <div class="text-sm font-semibold text-stone-400 uppercase tracking-wider mb-2">Enterprise</div>
                    <div class="flex items-baseline gap-1 mb-6">
                        <span class="text-4xl font-bold text-white">Custom</span>
                    </div>
                    <p class="text-stone-400 text-sm mb-6">Untuk jaringan rental besar (50+ unit)</p>
                    <ul class="space-y-3 mb-8 text-sm text-stone-300">
                        <li class="flex items-center gap-2"><span class="text-emerald-400">✓</span> Unlimited user</li>
                        <li class="flex items-center gap-2"><span class="text-emerald-400">✓</span> Source code lengkap</li>
                        <li class="flex items-center gap-2"><span class="text-emerald-400">✓</span> Multi-lokasi</li>
                        <li class="flex items-center gap-2"><span class="text-emerald-400">✓</span> Custom fitur sesuai kebutuhan</li>
                        <li class="flex items-center gap-2"><span class="text-emerald-400">✓</span> Deployment & training</li>
                        <li class="flex items-center gap-2"><span class="text-emerald-400">✓</span> Priority support 1 tahun</li>
                        <li class="flex items-center gap-2"><span class="text-emerald-400">✓</span> SLA guarantee</li>
                    </ul>
                    <a href="/contact" class="block w-full text-center py-3 rounded-xl border-2 border-stone-600 text-white font-semibold hover:border-stone-400 hover:bg-stone-800 transition-all">Hubungi Sales</a>
                </div>
            </div>
        </div>
    </section>

    {{-- Final CTA --}}
    <section class="py-24 lg:py-32 gradient-cta relative overflow-hidden">
        <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(circle at 30% 50%, rgba(99,102,241,0.3) 0%, transparent 50%), radial-gradient(circle at 70% 80%, rgba(165,180,252,0.2) 0%, transparent 50%)"></div>
        <div class="absolute top-10 right-10 text-[8rem] opacity-5 animate-float-slow select-none">🚗</div>
        <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-white mb-6 reveal">Mulai Kelola Rental Mobil Sekarang</h2>
            <p class="text-xl text-brand-200/80 mb-10 max-w-2xl mx-auto reveal stagger-1">Bisnis rental mobil Anda layak mendapatkan sistem terbaik. Mulai sekarang — gratis tanpa kartu kredit.</p>
            <div class="flex flex-col sm:flex-row justify-center gap-4 reveal stagger-2">
                <a href="/admin/login" class="inline-flex items-center justify-center gap-2 px-8 py-4 bg-white text-brand-700 font-bold rounded-xl hover:bg-brand-50 transition-all hover:shadow-xl hover:-translate-y-0.5">
                    🚗 Coba Demo Sekarang
                </a>
                <a href="/docs" class="inline-flex items-center justify-center gap-2 px-8 py-4 border-2 border-white/30 text-white font-semibold rounded-xl hover:bg-white/10 transition-all hover:-translate-y-0.5">
                    📖 Lihat Dokumentasi
                </a>
            </div>
        </div>
    </section>

    {{-- Footer --}}
    <footer class="bg-stone-950 text-stone-400 py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-10 mb-12">
                <div>
                    <div class="flex items-center gap-2 mb-4">
                        <span class="text-2xl">🚗</span>
                        <span class="font-bold text-xl text-white">RentalMobil</span>
                    </div>
                    <p class="text-sm leading-relaxed">Sistem manajemen rental mobil lengkap. Booking, armada, keuangan, dan keamanan — terintegrasi.</p>
                </div>
                <div>
                    <h4 class="font-semibold text-white text-sm uppercase tracking-wider mb-4">Produk</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#fitur" class="hover:text-white transition-colors">Fitur</a></li>
                        <li><a href="#harga" class="hover:text-white transition-colors">Harga</a></li>
                        <li><a href="/docs" class="hover:text-white transition-colors">Dokumentasi</a></li>
                        <li><a href="/blog" class="hover:text-white transition-colors">Blog</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold text-white text-sm uppercase tracking-wider mb-4">Perusahaan</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="/contact" class="hover:text-white transition-colors">Kontak</a></li>
                        <li><a href="/faq" class="hover:text-white transition-colors">FAQ</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Kebijakan Privasi</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Syarat & Ketentuan</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold text-white text-sm uppercase tracking-wider mb-4">Kontak</h4>
                    <ul class="space-y-2 text-sm">
                        <li>📞 +62 812-3456-7890</li>
                        <li>✉️ hello@rentalmobil.id</li>
                        <li>📍 Jakarta Selatan, Indonesia</li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-stone-800 pt-8 flex flex-col sm:flex-row justify-between items-center gap-4">
                <p class="text-xs">&copy; {{ date('Y') }} RentalMobil. All rights reserved.</p>
                <p class="text-xs text-stone-600">Powered by Laravel</p>
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const reveals = document.querySelectorAll('.reveal');
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                    }
                });
            }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });
            reveals.forEach(el => observer.observe(el));
        });
    </script>
</body>
</html>

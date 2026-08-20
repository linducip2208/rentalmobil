<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RentalMobil — Sewa Mobil Mudah & Terpercaya</title>
    <meta name="description" content="Sewa mobil terpercaya di Indonesia. Katalog lengkap, harga transparan, booking online 24/7. SUV, Sedan, MPV, ELF tersedia.">
    <meta name="keywords" content="sewa mobil, rental mobil, sewa mobil murah, rental mobil terpercaya, sewa mobil online">
    <meta property="og:title" content="RentalMobil — Sewa Mobil Mudah & Terpercaya">
    <meta property="og:description" content="Sewa mobil terpercaya di Indonesia. Katalog lengkap, harga transparan, booking online 24/7.">
    <meta property="og:type" content="website">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Playfair+Display:wght@700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        display: ['Playfair Display', 'serif'],
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a5f',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        @keyframes fadeSlideUp {
            0% { transform: translateY(40px); opacity: 0; }
            100% { transform: translateY(0); opacity: 1; }
        }
        @keyframes floatSlow {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-12px); }
        }
        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }
        @keyframes scaleIn {
            0% { transform: scale(0.85); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }
        @keyframes pingSlow {
            0% { transform: scale(1); opacity: 1; }
            100% { transform: scale(1.5); opacity: 0; }
        }
        .animate-float-slow { animation: floatSlow 5s ease-in-out infinite; }
        .animate-shimmer {
            background: linear-gradient(90deg, transparent 25%, rgba(255,255,255,.15) 50%, transparent 75%);
            background-size: 200% 100%;
            animation: shimmer 1.8s ease-in-out infinite;
        }
        .card-lift { transition: transform .35s, box-shadow .35s; }
        .card-lift:hover { transform: translateY(-6px); box-shadow: 0 24px 48px -12px rgba(0,0,0,.18); }
        .reveal { opacity: 0; transform: translateY(30px); transition: opacity .7s, transform .7s cubic-bezier(.16,1,.3,1); }
        .reveal.visible { opacity: 1; transform: translateY(0); }
        .stagger-1 { transition-delay: .1s; }
        .stagger-2 { transition-delay: .2s; }
        .stagger-3 { transition-delay: .3s; }
        .stagger-4 { transition-delay: .4s; }
        .stagger-5 { transition-delay: .5s; }
        .gradient-hero {
            background: linear-gradient(135deg, #1e3a5f 0%, #1e40af 40%, #2563eb 70%, #3b82f6 100%);
        }
        .gradient-cta {
            background: linear-gradient(135deg, #1e3a5f 0%, #0f172a 100%);
        }
        .glass {
            background: rgba(255,255,255,0.08);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255,255,255,0.12);
        }
        @media (prefers-reduced-motion: reduce) {
            .reveal, .card-lift, .animate-float-slow { animation-duration: 0.01ms !important; transition-duration: 0.01ms !important; }
        }
    </style>
</head>
<body class="font-sans bg-stone-50 text-stone-800 antialiased">

    {{-- Header --}}
    <header class="fixed top-0 left-0 right-0 z-50 transition-all duration-300" x-data="{ scrolled: false }" @scroll.window="scrolled = (window.scrollY > 40)">
        <div :class="scrolled ? 'bg-white/80 backdrop-blur-xl shadow-sm border-b border-stone-200/60' : 'bg-transparent'" class="transition-all duration-300">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-16 lg:h-18">
                <a href="{{ route('home') }}" class="flex items-center gap-2">
                    <span class="text-2xl">🚗</span>
                    <span class="font-display font-bold text-xl" :class="scrolled ? 'text-stone-900' : 'text-white'">RentalMobil</span>
                </a>
                <nav class="hidden md:flex items-center gap-8">
                    <a href="#fitur" :class="scrolled ? 'text-stone-600 hover:text-brand-600' : 'text-white/80 hover:text-white'" class="text-sm font-medium transition-colors">Fitur</a>
                    <a href="#galeri" :class="scrolled ? 'text-stone-600 hover:text-brand-600' : 'text-white/80 hover:text-white'" class="text-sm font-medium transition-colors">Galeri</a>
                    <a href="#harga" :class="scrolled ? 'text-stone-600 hover:text-brand-600' : 'text-white/80 hover:text-white'" class="text-sm font-medium transition-colors">Harga</a>
                    <a href="/docs" :class="scrolled ? 'text-stone-600 hover:text-brand-600' : 'text-white/80 hover:text-white'" class="text-sm font-medium transition-colors">Dokumentasi</a>
                    <a href="/blog" :class="scrolled ? 'text-stone-600 hover:text-brand-600' : 'text-white/80 hover:text-white'" class="text-sm font-medium transition-colors">Blog</a>
                    <a href="{{ route('portal.login') }}" class="px-5 py-2 bg-brand-600 text-white text-sm font-semibold rounded-lg hover:bg-brand-700 transition-all hover:shadow-lg hover:shadow-brand-600/25">Masuk</a>
                </nav>
                <button class="md:hidden" :class="scrolled ? 'text-stone-900' : 'text-white'" @click="$dispatch('toggle-mobile-menu')">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
            </div>
        </div>
    </header>

    {{-- Hero --}}
    <section class="relative min-h-screen gradient-hero overflow-hidden flex items-center">
        <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(circle at 20% 50%, rgba(96,165,250,0.4) 0%, transparent 50%), radial-gradient(circle at 80% 20%, rgba(147,197,253,0.3) 0%, transparent 50%)"></div>
        <div class="absolute -bottom-32 -right-32 text-[22rem] opacity-[0.06] animate-float-slow select-none">🚗</div>
        <div class="absolute top-20 left-10 text-[6rem] opacity-[0.06] animate-float-slow select-none" style="animation-delay: 1.5s">🏎️</div>
        <div class="absolute top-40 right-20 text-[4rem] opacity-[0.06] animate-float-slow select-none" style="animation-delay: 3s">🚙</div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-32 lg:py-40">
            <div class="max-w-3xl">
                <div class="reveal visible inline-flex items-center gap-2 glass rounded-full px-4 py-1.5 mb-6">
                    <span class="w-2 h-2 bg-emerald-400 rounded-full animate-pulse"></span>
                    <span class="text-brand-100 text-sm font-medium">Tersedia 24/7 di seluruh Indonesia</span>
                </div>
                <h1 class="font-display text-5xl sm:text-6xl lg:text-7xl font-bold text-white leading-[1.05] mb-6 reveal stagger-1 visible">
                    Sewa Mobil<br>
                    <span class="text-brand-200">Mudah & Terpercaya</span>
                </h1>
                <p class="text-lg sm:text-xl text-brand-100/90 leading-relaxed mb-10 max-w-xl reveal stagger-2 visible">
                    Temukan kendaraan terbaik untuk setiap kebutuhan. Dari city car hingga bus pariwisata — booking online, harga transparan, tanpa ribet.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 reveal stagger-3 visible">
                    <a href="#fitur" class="inline-flex items-center justify-center gap-2 px-8 py-4 bg-white text-brand-700 font-bold text-base rounded-xl hover:bg-brand-50 transition-all hover:shadow-xl hover:shadow-black/10 hover:-translate-y-0.5">
                        <span>🚗</span> Browse Mobil
                    </a>
                    <a href="/docs" class="inline-flex items-center justify-center gap-2 px-8 py-4 glass text-white font-semibold text-base rounded-xl hover:bg-white/20 transition-all hover:-translate-y-0.5">
                        Lihat Dokumentasi →
                    </a>
                </div>
                <div class="flex items-center gap-8 mt-14 reveal stagger-4 visible">
                    <div>
                        <div class="text-3xl font-bold text-white">250+</div>
                        <div class="text-sm text-brand-200/70">Unit Kendaraan</div>
                    </div>
                    <div class="w-px h-10 bg-white/20"></div>
                    <div>
                        <div class="text-3xl font-bold text-white">50K+</div>
                        <div class="text-sm text-brand-200/70">Pelanggan Puas</div>
                    </div>
                    <div class="w-px h-10 bg-white/20"></div>
                    <div>
                        <div class="text-3xl font-bold text-white">4.9★</div>
                        <div class="text-sm text-brand-200/70">Rating Google</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="absolute bottom-0 left-0 right-0 h-24 bg-gradient-to-t from-stone-50 to-transparent"></div>
    </section>

    {{-- Trust Strip --}}
    <section class="py-12 bg-white border-b border-stone-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <p class="text-center text-sm text-stone-400 font-medium mb-8 uppercase tracking-wider">Cocok untuk berbagai kebutuhan</p>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-6">
                <div class="flex flex-col items-center gap-3 card-lift cursor-default">
                    <div class="w-16 h-16 rounded-2xl bg-brand-50 flex items-center justify-center text-3xl">🏝️</div>
                    <span class="text-sm font-semibold text-stone-700">Wisatawan</span>
                </div>
                <div class="flex flex-col items-center gap-3 card-lift cursor-default">
                    <div class="w-16 h-16 rounded-2xl bg-emerald-50 flex items-center justify-center text-3xl">💼</div>
                    <span class="text-sm font-semibold text-stone-700">Bisnis</span>
                </div>
                <div class="flex flex-col items-center gap-3 card-lift cursor-default">
                    <div class="w-16 h-16 rounded-2xl bg-amber-50 flex items-center justify-center text-3xl">👨‍👩‍👧‍👦</div>
                    <span class="text-sm font-semibold text-stone-700">Keluarga</span>
                </div>
                <div class="flex flex-col items-center gap-3 card-lift cursor-default">
                    <div class="w-16 h-16 rounded-2xl bg-rose-50 flex items-center justify-center text-3xl">💒</div>
                    <span class="text-sm font-semibold text-stone-700">Pernikahan</span>
                </div>
                <div class="flex flex-col items-center gap-3 card-lift cursor-default">
                    <div class="w-16 h-16 rounded-2xl bg-violet-50 flex items-center justify-center text-3xl">📦</div>
                    <span class="text-sm font-semibold text-stone-700">Ekspedisi</span>
                </div>
            </div>
        </div>
    </section>

    {{-- Problem / Solution --}}
    <section class="py-20 lg:py-28">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="font-display text-4xl lg:text-5xl font-bold text-stone-900 mb-4 reveal">Masalah yang Sering Dihadapi</h2>
                <p class="text-lg text-stone-500 max-w-2xl mx-auto reveal stagger-1">Kami paham frustrasi rental mobil konvensional. Inilah solusi kami.</p>
            </div>
            <div class="grid md:grid-cols-3 gap-8">
                {{-- Pair 1 --}}
                <div class="reveal stagger-1">
                    <div class="bg-red-50 border border-red-100 rounded-2xl p-6 mb-4">
                        <div class="text-red-500 text-sm font-semibold mb-2">❌ Sebelumnya</div>
                        <p class="text-stone-700 font-medium">Harga tersembunyi, biaya tambahan muncul saat pengembalian kendaraan</p>
                    </div>
                    <div class="bg-emerald-50 border border-emerald-100 rounded-2xl p-6">
                        <div class="text-emerald-600 text-sm font-semibold mb-2">✅ Solusi Kami</div>
                        <p class="text-stone-700 font-medium">Harga transparan dari awal — apa yang Anda lihat, itulah yang Anda bayar</p>
                    </div>
                </div>
                {{-- Pair 2 --}}
                <div class="reveal stagger-2">
                    <div class="bg-red-50 border border-red-100 rounded-2xl p-6 mb-4">
                        <div class="text-red-500 text-sm font-semibold mb-2">❌ Sebelumnya</div>
                        <p class="text-stone-700 font-medium">Antri berjam-jam di lokasi, proses sewa memakan waktu lama</p>
                    </div>
                    <div class="bg-emerald-50 border border-emerald-100 rounded-2xl p-6">
                        <div class="text-emerald-600 text-sm font-semibold mb-2">✅ Solusi Kami</div>
                        <p class="text-stone-700 font-medium">Booking online 24/7, konfirmasi instan, kendaraan siap saat Anda tiba</p>
                    </div>
                </div>
                {{-- Pair 3 --}}
                <div class="reveal stagger-3">
                    <div class="bg-red-50 border border-red-100 rounded-2xl p-6 mb-4">
                        <div class="text-red-500 text-sm font-semibold mb-2">❌ Sebelumnya</div>
                        <p class="text-stone-700 font-medium">Kondisi kendaraan tidak jelas, sering mogok di perjalanan</p>
                    </div>
                    <div class="bg-emerald-50 border border-emerald-100 rounded-2xl p-6">
                        <div class="text-emerald-600 text-sm font-semibold mb-2">✅ Solusi Kami</div>
                        <p class="text-stone-700 font-medium">Armada terawat berkala, dilengkapi asuransi dan bantuan darurat 24 jam</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Stats Bar --}}
    <section class="py-12 gradient-hero relative overflow-hidden">
        <div class="absolute inset-0 opacity-10" style="background-image: repeating-linear-gradient(90deg, transparent, transparent 40px, rgba(255,255,255,0.05) 40px, rgba(255,255,255,0.05) 80px)"></div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-8 text-center">
                <div class="reveal">
                    <div class="text-4xl lg:text-5xl font-bold text-white mb-1">150+</div>
                    <div class="text-brand-200/80 text-sm font-medium">Jenis Kendaraan</div>
                </div>
                <div class="reveal stagger-1">
                    <div class="text-4xl lg:text-5xl font-bold text-white mb-1">34</div>
                    <div class="text-brand-200/80 text-sm font-medium">Kota Terjangkau</div>
                </div>
                <div class="reveal stagger-2">
                    <div class="text-4xl lg:text-5xl font-bold text-white mb-1">200+</div>
                    <div class="text-brand-200/80 text-sm font-medium">Rute Tersedia</div>
                </div>
                <div class="reveal stagger-3">
                    <div class="text-4xl lg:text-5xl font-bold text-white mb-1">99.8%</div>
                    <div class="text-brand-200/80 text-sm font-medium">Uptime Layanan</div>
                </div>
            </div>
        </div>
    </section>

    {{-- Features --}}
    <section id="fitur" class="py-20 lg:py-28">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-20">
                <h2 class="font-display text-4xl lg:text-5xl font-bold text-stone-900 mb-4 reveal">Fitur Unggulan</h2>
                <p class="text-lg text-stone-500 max-w-2xl mx-auto reveal stagger-1">Semua yang Anda butuhkan untuk pengalaman rental mobil terbaik</p>
            </div>

            {{-- Feature 1 --}}
            <div class="flex flex-col lg:flex-row items-center gap-12 lg:gap-20 mb-24 reveal">
                <div class="w-full lg:w-1/2">
                    <div class="relative rounded-2xl overflow-hidden shadow-2xl bg-gradient-to-br from-brand-100 to-brand-200 aspect-[4/3] flex items-center justify-center">
                        <div class="text-center">
                            <div class="text-8xl mb-4">🚗</div>
                            <div class="text-brand-600 font-bold text-lg">Katalog Mobil</div>
                            <div class="text-brand-400 text-sm mt-1">Screenshot placeholder</div>
                        </div>
                        <div class="absolute top-3 left-3 flex gap-1.5">
                            <div class="w-3 h-3 rounded-full bg-red-400"></div>
                            <div class="w-3 h-3 rounded-full bg-amber-400"></div>
                            <div class="w-3 h-3 rounded-full bg-emerald-400"></div>
                        </div>
                        <div class="absolute top-3 left-12 right-3 h-6 bg-white/30 rounded-md flex items-center px-3">
                            <span class="text-xs text-brand-600 font-mono">rentalmobil.id/katalog</span>
                        </div>
                    </div>
                </div>
                <div class="w-full lg:w-1/2">
                    <h3 class="font-display text-3xl font-bold text-stone-900 mb-4">Katalog Mobil Lengkap</h3>
                    <p class="text-stone-600 text-lg leading-relaxed mb-6">Pilih dari 150+ unit kendaraan yang tersedia. Setiap kendaraan memiliki foto asli, spesifikasi detail, dan kondisi terkini.</p>
                    <ul class="space-y-3">
                        <li class="flex items-center gap-3"><span class="w-6 h-6 rounded-full bg-brand-100 text-brand-600 flex items-center justify-center text-sm">✓</span><span class="text-stone-700">Filter berdasarkan tipe, harga, dan kota</span></li>
                        <li class="flex items-center gap-3"><span class="w-6 h-6 rounded-full bg-brand-100 text-brand-600 flex items-center justify-center text-sm">✓</span><span class="text-stone-700">Foto 360° untuk setiap kendaraan</span></li>
                        <li class="flex items-center gap-3"><span class="w-6 h-6 rounded-full bg-brand-100 text-brand-600 flex items-center justify-center text-sm">✓</span><span class="text-stone-700">Status ketersediaan real-time</span></li>
                        <li class="flex items-center gap-3"><span class="w-6 h-6 rounded-full bg-brand-100 text-brand-600 flex items-center justify-center text-sm">✓</span><span class="text-stone-700">Detail lengkap: BBM, kapasitas, tahun</span></li>
                        <li class="flex items-center gap-3"><span class="w-6 h-6 rounded-full bg-brand-100 text-brand-600 flex items-center justify-center text-sm">✓</span><span class="text-stone-700">Ulasan dan rating dari penyewa sebelumnya</span></li>
                    </ul>
                </div>
            </div>

            {{-- Feature 2 --}}
            <div class="flex flex-col lg:flex-row-reverse items-center gap-12 lg:gap-20 mb-24 reveal">
                <div class="w-full lg:w-1/2">
                    <div class="relative rounded-2xl overflow-hidden shadow-2xl bg-gradient-to-br from-emerald-100 to-emerald-200 aspect-[4/3] flex items-center justify-center">
                        <div class="text-center">
                            <div class="text-8xl mb-4">📱</div>
                            <div class="text-emerald-600 font-bold text-lg">Booking Online</div>
                            <div class="text-emerald-400 text-sm mt-1">Screenshot placeholder</div>
                        </div>
                        <div class="absolute top-3 left-3 flex gap-1.5">
                            <div class="w-3 h-3 rounded-full bg-red-400"></div>
                            <div class="w-3 h-3 rounded-full bg-amber-400"></div>
                            <div class="w-3 h-3 rounded-full bg-emerald-400"></div>
                        </div>
                        <div class="absolute top-3 left-12 right-3 h-6 bg-white/30 rounded-md flex items-center px-3">
                            <span class="text-xs text-emerald-600 font-mono">rentalmobil.id/booking</span>
                        </div>
                    </div>
                </div>
                <div class="w-full lg:w-1/2">
                    <h3 class="font-display text-3xl font-bold text-stone-900 mb-4">Booking Online 24/7</h3>
                    <p class="text-stone-600 text-lg leading-relaxed mb-6">Sewa mobil kapan saja, di mana saja. Proses booking selesai dalam 3 menit — pilih kendaraan, tentukan tanggal, bayar, selesai.</p>
                    <ul class="space-y-3">
                        <li class="flex items-center gap-3"><span class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-sm">✓</span><span class="text-stone-700">Konfirmasi instan via WhatsApp & email</span></li>
                        <li class="flex items-center gap-3"><span class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-sm">✓</span><span class="text-stone-700">Pembayaran kartu kredit, transfer, e-wallet</span></li>
                        <li class="flex items-center gap-3"><span class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-sm">✓</span><span class="text-stone-700">Kalender ketersediaan real-time</span></li>
                        <li class="flex items-center gap-3"><span class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-sm">✓</span><span class="text-stone-700">Booking berulang untuk pelanggan setia</span></li>
                        <li class="flex items-center gap-3"><span class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-sm">✓</span><span class="text-stone-700">Grup booking untuk rombongan</span></li>
                    </ul>
                </div>
            </div>

            {{-- Feature 3 --}}
            <div class="flex flex-col lg:flex-row items-center gap-12 lg:gap-20 mb-24 reveal">
                <div class="w-full lg:w-1/2">
                    <div class="relative rounded-2xl overflow-hidden shadow-2xl bg-gradient-to-br from-amber-100 to-amber-200 aspect-[4/3] flex items-center justify-center">
                        <div class="text-center">
                            <div class="text-8xl mb-4">💰</div>
                            <div class="text-amber-600 font-bold text-lg">Harga Transparan</div>
                            <div class="text-amber-400 text-sm mt-1">Screenshot placeholder</div>
                        </div>
                        <div class="absolute top-3 left-3 flex gap-1.5">
                            <div class="w-3 h-3 rounded-full bg-red-400"></div>
                            <div class="w-3 h-3 rounded-full bg-amber-400"></div>
                            <div class="w-3 h-3 rounded-full bg-emerald-400"></div>
                        </div>
                        <div class="absolute top-3 left-12 right-3 h-6 bg-white/30 rounded-md flex items-center px-3">
                            <span class="text-xs text-amber-600 font-mono">rentalmobil.id/harga</span>
                        </div>
                    </div>
                </div>
                <div class="w-full lg:w-1/2">
                    <h3 class="font-display text-3xl font-bold text-stone-900 mb-4">Harga Transparan</h3>
                    <p class="text-stone-600 text-lg leading-relaxed mb-6">Tidak ada biaya tersembunyi. Harga yang tertera adalah harga akhir — termasuk asuransi, pajak, dan biaya layanan.</p>
                    <ul class="space-y-3">
                        <li class="flex items-center gap-3"><span class="w-6 h-6 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center text-sm">✓</span><span class="text-stone-700">Perbandingan harga antar kendaraan</span></li>
                        <li class="flex items-center gap-3"><span class="w-6 h-6 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center text-sm">✓</span><span class="text-stone-700">Diskon untuk penyewaan mingguan & bulanan</span></li>
                        <li class="flex items-center gap-3"><span class="w-6 h-6 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center text-sm">✓</span><span class="text-stone-700">Invoice detail untuk klaim kantor</span></li>
                        <li class="flex items-center gap-3"><span class="w-6 h-6 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center text-sm">✓</span><span class="text-stone-700">Promo & coupon code tersedia</span></li>
                        <li class="flex items-center gap-3"><span class="w-6 h-6 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center text-sm">✓</span><span class="text-stone-700">Estimasi biaya sebelum booking</span></li>
                    </ul>
                </div>
            </div>

            {{-- Feature 4 --}}
            <div class="flex flex-col lg:flex-row-reverse items-center gap-12 lg:gap-20 mb-24 reveal">
                <div class="w-full lg:w-1/2">
                    <div class="relative rounded-2xl overflow-hidden shadow-2xl bg-gradient-to-br from-violet-100 to-violet-200 aspect-[4/3] flex items-center justify-center">
                        <div class="text-center">
                            <div class="text-8xl mb-4">🛡️</div>
                            <div class="text-violet-600 font-bold text-lg">Asuransi Kendaraan</div>
                            <div class="text-violet-400 text-sm mt-1">Screenshot placeholder</div>
                        </div>
                        <div class="absolute top-3 left-3 flex gap-1.5">
                            <div class="w-3 h-3 rounded-full bg-red-400"></div>
                            <div class="w-3 h-3 rounded-full bg-amber-400"></div>
                            <div class="w-3 h-3 rounded-full bg-emerald-400"></div>
                        </div>
                        <div class="absolute top-3 left-12 right-3 h-6 bg-white/30 rounded-md flex items-center px-3">
                            <span class="text-xs text-violet-600 font-mono">rentalmobil.id/asuransi</span>
                        </div>
                    </div>
                </div>
                <div class="w-full lg:w-1/2">
                    <h3 class="font-display text-3xl font-bold text-stone-900 mb-4">Asuransi Kendaraan</h3>
                    <p class="text-stone-600 text-lg leading-relaxed mb-6">Setiap kendaraan dilindungi asuransi all-risk. Tidak perlu khawatir — biaya perbaikan ditanggung penuh oleh asuransi.</p>
                    <ul class="space-y-3">
                        <li class="flex items-center gap-3"><span class="w-6 h-6 rounded-full bg-violet-100 text-violet-600 flex items-center justify-center text-sm">✓</span><span class="text-stone-700">Asuransi all-risk termasuk dalam harga</span></li>
                        <li class="flex items-center gap-3"><span class="w-6 h-6 rounded-full bg-violet-100 text-violet-600 flex items-center justify-center text-sm">✓</span><span class="text-stone-700">Cacat bodi ≤ 30cm tanpa klaim</span></li>
                        <li class="flex items-center gap-3"><span class="w-6 h-6 rounded-full bg-violet-100 text-violet-600 flex items-center justify-center text-sm">✓</span><span class="text-stone-700">Layanan derek 24 jam</span></li>
                        <li class="flex items-center gap-3"><span class="w-6 h-6 rounded-full bg-violet-100 text-violet-600 flex items-center justify-center text-sm">✓</span><span class="text-stone-700">Penggantian kendaraan saat perbaikan</span></li>
                        <li class="flex items-center gap-3"><span class="w-6 h-6 rounded-full bg-violet-100 text-violet-600 flex items-center justify-center text-sm">✓</span><span class="text-stone-700">Proses klaim cepat & mudah</span></li>
                    </ul>
                </div>
            </div>

            {{-- Feature 5 --}}
            <div class="flex flex-col lg:flex-row items-center gap-12 lg:gap-20 mb-24 reveal">
                <div class="w-full lg:w-1/2">
                    <div class="relative rounded-2xl overflow-hidden shadow-2xl bg-gradient-to-br from-rose-100 to-rose-200 aspect-[4/3] flex items-center justify-center">
                        <div class="text-center">
                            <div class="text-8xl mb-4">👨‍✈️</div>
                            <div class="text-rose-600 font-bold text-lg">Driver Berpengalaman</div>
                            <div class="text-rose-400 text-sm mt-1">Screenshot placeholder</div>
                        </div>
                        <div class="absolute top-3 left-3 flex gap-1.5">
                            <div class="w-3 h-3 rounded-full bg-red-400"></div>
                            <div class="w-3 h-3 rounded-full bg-amber-400"></div>
                            <div class="w-3 h-3 rounded-full bg-emerald-400"></div>
                        </div>
                        <div class="absolute top-3 left-12 right-3 h-6 bg-white/30 rounded-md flex items-center px-3">
                            <span class="text-xs text-rose-600 font-mono">rentalmobil.id/driver</span>
                        </div>
                    </div>
                </div>
                <div class="w-full lg:w-1/2">
                    <h3 class="font-display text-3xl font-bold text-stone-900 mb-4">Driver Berpengalaman</h3>
                    <p class="text-stone-600 text-lg leading-relaxed mb-6">Tersedia driver profesional bersertifikat untuk perjalanan dalam dan luar kota. Sudah berpengalaman di berbagai rute.</p>
                    <ul class="space-y-3">
                        <li class="flex items-center gap-3"><span class="w-6 h-6 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center text-sm">✓</span><span class="text-stone-700">Driver bersertifikat & berlisensi</span></li>
                        <li class="flex items-center gap-3"><span class="w-6 h-6 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center text-sm">✓</span><span class="text-stone-700">Hafal rute wisata & perkotaan</span></li>
                        <li class="flex items-center gap-3"><span class="w-6 h-6 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center text-sm">✓</span><span class="text-stone-700">Tepat waktu & profesional</span></li>
                        <li class="flex items-center gap-3"><span class="w-6 h-6 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center text-sm">✓</span><span class="text-stone-700">Pilihan driver pria/wanita</span></li>
                        <li class="flex items-center gap-3"><span class="w-6 h-6 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center text-sm">✓</span><span class="text-stone-700">Tarif flat per hari, tidak meteran</span></li>
                    </ul>
                </div>
            </div>

            {{-- Feature 6 --}}
            <div class="flex flex-col lg:flex-row-reverse items-center gap-12 lg:gap-20 mb-24 reveal">
                <div class="w-full lg:w-1/2">
                    <div class="relative rounded-2xl overflow-hidden shadow-2xl bg-gradient-to-br from-cyan-100 to-cyan-200 aspect-[4/3] flex items-center justify-center">
                        <div class="text-center">
                            <div class="text-8xl mb-4">📡</div>
                            <div class="text-cyan-600 font-bold text-lg">Pelacakan GPS</div>
                            <div class="text-cyan-400 text-sm mt-1">Screenshot placeholder</div>
                        </div>
                        <div class="absolute top-3 left-3 flex gap-1.5">
                            <div class="w-3 h-3 rounded-full bg-red-400"></div>
                            <div class="w-3 h-3 rounded-full bg-amber-400"></div>
                            <div class="w-3 h-3 rounded-full bg-emerald-400"></div>
                        </div>
                        <div class="absolute top-3 left-12 right-3 h-6 bg-white/30 rounded-md flex items-center px-3">
                            <span class="text-xs text-cyan-600 font-mono">rentalmobil.id/tracking</span>
                        </div>
                    </div>
                </div>
                <div class="w-full lg:w-1/2">
                    <h3 class="font-display text-3xl font-bold text-stone-900 mb-4">Pelacakan GPS Real-time</h3>
                    <p class="text-stone-600 text-lg leading-relaxed mb-6">Pantau lokasi kendaraan secara real-time melalui dashboard. Fitur ini membantu Anda merencanakan perjalanan dan memantau armada.</p>
                    <ul class="space-y-3">
                        <li class="flex items-center gap-3"><span class="w-6 h-6 rounded-full bg-cyan-100 text-cyan-600 flex items-center justify-center text-sm">✓</span><span class="text-stone-700">Live tracking via web & mobile</span></li>
                        <li class="flex items-center gap-3"><span class="w-6 h-6 rounded-full bg-cyan-100 text-cyan-600 flex items-center justify-center text-sm">✓</span><span class="text-stone-700">Geofence & notifikasi area</span></li>
                        <li class="flex items-center gap-3"><span class="w-6 h-6 rounded-full bg-cyan-100 text-cyan-600 flex items-center justify-center text-sm">✓</span><span class="text-stone-700">Riwayat perjalanan (history route)</span></li>
                        <li class="flex items-center gap-3"><span class="w-6 h-6 rounded-full bg-cyan-100 text-cyan-600 flex items-center justify-center text-sm">✓</span><span class="text-stone-700">Integrasi Google Maps & Waze</span></li>
                        <li class="flex items-center gap-3"><span class="w-6 h-6 rounded-full bg-cyan-100 text-cyan-600 flex items-center justify-center text-sm">✓</span><span class="text-stone-700">Alert kecepatan & penggunaan BBM</span></li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    {{-- Screenshot Gallery --}}
    <section id="galeri" class="py-20 lg:py-28 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="font-display text-4xl lg:text-5xl font-bold text-stone-900 mb-4 reveal">Tampilan Aplikasi</h2>
                <p class="text-lg text-stone-500 max-w-2xl mx-auto reveal stagger-1">Jelajahi antarmuka yang dirancang untuk kemudahan</p>
            </div>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach([
                    ['icon' => '🏠', 'title' => 'Dashboard Admin', 'color' => 'from-brand-50 to-brand-100', 'text' => 'brand'],
                    ['icon' => '🚗', 'title' => 'Katalog Kendaraan', 'color' => 'from-emerald-50 to-emerald-100', 'text' => 'emerald'],
                    ['icon' => '📋', 'title' => 'Manajemen Booking', 'color' => 'from-amber-50 to-amber-100', 'text' => 'amber'],
                    ['icon' => '🧾', 'title' => 'Invoice & Pembayaran', 'color' => 'from-violet-50 to-violet-100', 'text' => 'violet'],
                    ['icon' => '📊', 'title' => 'Laporan & Analitik', 'color' => 'from-rose-50 to-rose-100', 'text' => 'rose'],
                    ['icon' => '📱', 'title' => 'Portal Pelanggan', 'color' => 'from-cyan-50 to-cyan-100', 'text' => 'cyan'],
                ] as $screen)
                <div class="group card-lift reveal">
                    <div class="relative rounded-2xl overflow-hidden shadow-lg bg-gradient-to-br {{ $screen['color'] }} aspect-[16/10] flex items-center justify-center">
                        <div class="text-center group-hover:scale-105 transition-transform duration-300">
                            <div class="text-6xl mb-3">{{ $screen['icon'] }}</div>
                            <div class="text-{{ $screen['text'] }}-700 font-bold">{{ $screen['title'] }}</div>
                            <div class="text-{{ $screen['text'] }}-400 text-xs mt-1">Screenshot</div>
                        </div>
                        <div class="absolute top-3 left-3 flex gap-1.5">
                            <div class="w-2.5 h-2.5 rounded-full bg-red-400"></div>
                            <div class="w-2.5 h-2.5 rounded-full bg-amber-400"></div>
                            <div class="w-2.5 h-2.5 rounded-full bg-emerald-400"></div>
                        </div>
                    </div>
                    <p class="mt-3 text-sm font-medium text-stone-600 text-center">{{ $screen['title'] }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Demo Accounts --}}
    <section class="py-20 lg:py-28">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="font-display text-4xl font-bold text-stone-900 mb-4 reveal">Akun Demo</h2>
                <p class="text-lg text-stone-500 reveal stagger-1">Coba langsung aplikasi kami dengan akun demo berikut</p>
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
                                <td class="px-6 py-4 font-mono text-stone-600">owner@rentalmobil.test</td>
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
                                <td class="px-6 py-4 font-mono text-stone-600">admin@rentalmobil.test</td>
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
                                <td class="px-6 py-4"><span class="inline-flex items-center gap-1.5 font-semibold text-stone-900"><span class="w-2 h-2 bg-cyan-500 rounded-full"></span> Pelanggan</span></td>
                                <td class="px-6 py-4 font-mono text-stone-600">pelanggan@rentalmobil.test</td>
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
                <h2 class="font-display text-4xl lg:text-5xl font-bold text-stone-900 mb-4 reveal">Paket Harga</h2>
                <p class="text-lg text-stone-500 max-w-2xl mx-auto reveal stagger-1">Pilih paket yang sesuai dengan kebutuhan bisnis Anda</p>
            </div>
            <div class="grid md:grid-cols-3 gap-8 max-w-5xl mx-auto">
                {{-- Free --}}
                <div class="bg-stone-50 border border-stone-200 rounded-2xl p-8 card-lift reveal stagger-1">
                    <div class="text-sm font-semibold text-stone-500 uppercase tracking-wider mb-2">Free</div>
                    <div class="flex items-baseline gap-1 mb-6">
                        <span class="text-4xl font-bold text-stone-900">Rp 0</span>
                        <span class="text-stone-500">/bulan</span>
                    </div>
                    <ul class="space-y-3 mb-8 text-sm text-stone-600">
                        <li class="flex items-center gap-2"><span class="text-emerald-500">✓</span> 1 admin user</li>
                        <li class="flex items-center gap-2"><span class="text-emerald-500">✓</span> 10 booking/bulan</li>
                        <li class="flex items-center gap-2"><span class="text-emerald-500">✓</span> Katalog kendaraan</li>
                        <li class="flex items-center gap-2"><span class="text-emerald-500">✓</span> Invoice otomatis</li>
                        <li class="flex items-center gap-2 text-stone-400"><span>✕</span> GPS tracking</li>
                        <li class="flex items-center gap-2 text-stone-400"><span>✕</span> Laporan analitik</li>
                    </ul>
                    <a href="/admin/login" class="block w-full text-center py-3 rounded-xl border-2 border-stone-300 font-semibold text-stone-700 hover:border-stone-400 hover:bg-stone-100 transition-all">Mulai Gratis</a>
                </div>
                {{-- Growth --}}
                <div class="bg-brand-600 text-white rounded-2xl p-8 card-lift shadow-2xl shadow-brand-600/25 relative reveal stagger-2 scale-105">
                    <div class="absolute -top-4 left-1/2 -translate-x-1/2 bg-amber-400 text-amber-900 text-xs font-bold px-4 py-1 rounded-full uppercase tracking-wider">Populer</div>
                    <div class="text-sm font-semibold text-brand-200 uppercase tracking-wider mb-2">Growth</div>
                    <div class="flex items-baseline gap-1 mb-6">
                        <span class="text-4xl font-bold text-white">Rp 499rb</span>
                        <span class="text-brand-200">/bulan</span>
                    </div>
                    <ul class="space-y-3 mb-8 text-sm text-brand-100">
                        <li class="flex items-center gap-2"><span class="text-emerald-300">✓</span> 10 admin user</li>
                        <li class="flex items-center gap-2"><span class="text-emerald-300">✓</span> Unlimited booking</li>
                        <li class="flex items-center gap-2"><span class="text-emerald-300">✓</span> GPS tracking real-time</li>
                        <li class="flex items-center gap-2"><span class="text-emerald-300">✓</span> Laporan lengkap</li>
                        <li class="flex items-center gap-2"><span class="text-emerald-300">✓</span> Integrasi payment gateway</li>
                        <li class="flex items-center gap-2"><span class="text-emerald-300">✓</span> Portal pelanggan</li>
                    </ul>
                    <a href="/admin/login" class="block w-full text-center py-3 rounded-xl bg-white text-brand-700 font-bold hover:bg-brand-50 transition-all hover:shadow-lg">Pilih Growth</a>
                </div>
                {{-- Whitelabel --}}
                <div class="bg-stone-900 text-white rounded-2xl p-8 card-lift reveal stagger-3">
                    <div class="text-sm font-semibold text-stone-400 uppercase tracking-wider mb-2">Whitelabel</div>
                    <div class="flex items-baseline gap-1 mb-6">
                        <span class="text-4xl font-bold text-white">Custom</span>
                    </div>
                    <ul class="space-y-3 mb-8 text-sm text-stone-300">
                        <li class="flex items-center gap-2"><span class="text-emerald-400">✓</span> Unlimited user</li>
                        <li class="flex items-center gap-2"><span class="text-emerald-400">✓</span> Source code lengkap</li>
                        <li class="flex items-center gap-2"><span class="text-emerald-400">✓</span> Custom domain & branding</li>
                        <li class="flex items-center gap-2"><span class="text-emerald-400">✓</span> Custom fitur sesuai kebutuhan</li>
                        <li class="flex items-center gap-2"><span class="text-emerald-400">✓</span> Deployment & training</li>
                        <li class="flex items-center gap="><span class="text-emerald-400">✓</span> Priority support 1 tahun</li>
                    </ul>
                    <a href="https://wa.me/6281234567890" target="_blank" class="block w-full text-center py-3 rounded-xl border-2 border-stone-600 text-white font-semibold hover:border-stone-400 hover:bg-stone-800 transition-all">Hubungi Sales</a>
                </div>
            </div>
        </div>
    </section>

    {{-- Final CTA --}}
    <section class="py-24 lg:py-32 gradient-cta relative overflow-hidden">
        <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(circle at 30% 50%, rgba(59,130,246,0.3) 0%, transparent 50%), radial-gradient(circle at 70% 80%, rgba(147,197,253,0.2) 0%, transparent 50%)"></div>
        <div class="absolute top-10 right-10 text-[8rem] opacity-5 animate-float-slow select-none">🚗</div>
        <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="font-display text-4xl sm:text-5xl lg:text-6xl font-bold text-white mb-6 reveal">Siap Memulai?</h2>
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
                        <span class="font-display font-bold text-xl text-white">RentalMobil</span>
                    </div>
                    <p class="text-sm leading-relaxed">Sewa mobil mudah dan terpercaya di seluruh Indonesia. Armada terawat, harga transparan, layanan 24/7.</p>
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

<!DOCTYPE html>
@php($brand = app(\App\Services\WhitelabelService::class)->viewData())
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $pageTitle ?? $brand['name'].' — Sewa Mobil Premium' }}</title>
    <meta name="description" content="{{ $seoDescription ?? 'Sewa mobil harian, mingguan, atau bulanan dengan proses cepat, harga transparan, dan armada terawat. Lepas kunci atau dengan sopir.' }}">
    <link rel="icon" href="{{ $brand['favicon'] }}">
    @if(isset($seoCanonical))<link rel="canonical" href="{{ $seoCanonical }}">@endif
    @if(isset($seoJsonLd))<script type="application/ld+json">{!! $seoJsonLd !!}</script>@endif
    <meta property="og:title" content="{{ $pageTitle ?? $brand['name'] }}">
    <meta property="og:description" content="{{ $seoDescription ?? 'Sewa mobil dengan proses cepat dan harga transparan.' }}">
    <meta property="og:type" content="{{ $ogType ?? 'website' }}">
    @if(isset($ogImage))<meta property="og:image" content="{{ $ogImage }}">@endif
    <meta name="twitter:card" content="summary_large_image">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>:root { --brand-primary: {{ $brand['primaryColor'] }}; }</style>
    @stack('head')
</head>
<body class="min-h-screen bg-[#f6f8fa] font-sans text-slate-900 antialiased" x-data="{ menuOpen: false }">
    <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-[100] focus:rounded-lg focus:bg-fleet-950 focus:px-4 focus:py-2 focus:text-white">Lewati ke konten utama</a>

    <header class="sticky top-0 z-50 border-b border-white/10 bg-fleet-950/95 text-white backdrop-blur supports-[backdrop-filter]:bg-fleet-950/85">
        <nav class="mx-auto flex h-18 max-w-7xl items-center justify-between px-5 py-3 lg:px-8" aria-label="Navigasi utama">
            <a href="{{ route('home') }}" class="flex items-center gap-3 font-bold text-lg tracking-tight" aria-label="{{ $brand['name'] }} — Beranda">
                @if($brand['logo'])
                    <img src="{{ $brand['logo'] }}" alt="{{ $brand['name'] }}" class="h-9 max-w-40 object-contain">
                @else
                    <span class="grid h-10 w-10 place-items-center rounded-xl bg-gradient-to-br from-sky-500 to-blue-700 text-sm font-black text-white">{{ $brand['initials'] }}</span>
                    <span class="font-extrabold">{{ $brand['name'] }}</span>
                @endif
            </a>

            <div class="hidden items-center gap-7 text-sm font-semibold text-slate-300 lg:flex">
                <a href="{{ route('home') }}" class="transition hover:text-white">Beranda</a>
                <a href="{{ route('storefront.catalog') }}" class="transition hover:text-white">Sewa Mobil</a>
                <a href="{{ route('storefront.locations') }}" class="transition hover:text-white">Lokasi</a>
                <a href="{{ route('storefront.how-it-works') }}" class="transition hover:text-white">Cara Sewa</a>
                <a href="{{ route('faq.index') }}" class="transition hover:text-white">FAQ</a>
            </div>

            <div class="hidden items-center gap-3 lg:flex">
                @auth('customer')
                    <a href="{{ route('portal.dashboard') }}" class="rounded-xl border border-white/20 px-4 py-2.5 text-sm font-bold transition hover:bg-white/10">Akun Saya</a>
                @endauth
                @guest('customer')
                    <a href="{{ route('portal.login') }}" class="px-3 py-2 text-sm font-bold transition hover:text-white">Masuk</a>
                    <a href="{{ route('portal.login') }}" class="rounded-xl border border-white/20 px-4 py-2.5 text-sm font-bold transition hover:bg-white/10">Daftar</a>
                @endguest
                @auth('web')
                    <a href="{{ route('lte.dashboard') }}" class="rounded-xl bg-white px-4 py-2.5 text-sm font-extrabold text-fleet-950 transition hover:bg-slate-200">Masuk Admin</a>
                @endauth
                @guest('web')
                    <a href="{{ route('booking.index') }}" class="rounded-xl bg-white px-5 py-2.5 text-sm font-extrabold text-fleet-950 transition hover:bg-slate-200">Booking Saya</a>
                @endguest
            </div>

            <button type="button" class="grid h-11 w-11 place-items-center rounded-xl border border-white/20 lg:hidden" @click="menuOpen = !menuOpen" :aria-expanded="menuOpen" aria-controls="mobile-menu" aria-label="Buka menu navigasi">
                <svg x-show="!menuOpen" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7h16M4 12h16M4 17h16"/></svg>
                <svg x-show="menuOpen" x-cloak class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </nav>

        <div id="mobile-menu" x-show="menuOpen" x-cloak x-transition.origin.top class="grid gap-1 border-t border-white/10 bg-fleet-950 px-5 pb-6 pt-3 text-sm font-bold lg:hidden">
            <a class="rounded-lg p-3 transition hover:bg-white/10" href="{{ route('home') }}">Beranda</a>
            <a class="rounded-lg p-3 transition hover:bg-white/10" href="{{ route('storefront.catalog') }}">Sewa Mobil</a>
            <a class="rounded-lg p-3 transition hover:bg-white/10" href="{{ route('storefront.locations') }}">Lokasi</a>
            <a class="rounded-lg p-3 transition hover:bg-white/10" href="{{ route('storefront.how-it-works') }}">Cara Sewa</a>
            <a class="rounded-lg p-3 transition hover:bg-white/10" href="{{ route('faq.index') }}">FAQ</a>
            <div class="mt-3 grid gap-2">
                @auth('customer')
                    <a class="rounded-xl border border-white/20 p-3 text-center" href="{{ route('portal.dashboard') }}">Akun Saya</a>
                @endauth
                @guest('customer')
                    <a class="rounded-xl border border-white/20 p-3 text-center" href="{{ route('portal.login') }}">Masuk / Daftar</a>
                @endguest
                <a class="rounded-xl bg-white p-3 text-center text-fleet-950" href="{{ route('booking.index') }}">Booking Saya</a>
            </div>
        </div>
    </header>

    <main id="main" class="flex-1">
        @yield('content')
    </main>

    <footer class="mt-20 bg-fleet-950 pb-10 pt-16 text-slate-400">
        <div class="mx-auto grid max-w-7xl gap-12 px-5 lg:grid-cols-[1.4fr_1fr_1fr_1fr] lg:px-8">
            <div>
                <strong class="font-display text-xl font-extrabold text-white">{{ $brand['name'] }}</strong>
                <p class="mt-4 max-w-sm text-sm leading-6">Sewa mobil harian, mingguan, dan bulanan untuk perjalanan pribadi maupun perusahaan. Armada terawat, harga transparan, proses digital.</p>
                <p class="mt-5 text-sm">021-555-0101 · info@rentalmobil.test</p>
            </div>
            <div>
                <h2 class="text-xs font-extrabold uppercase tracking-[0.16em] text-white">Layanan</h2>
                <div class="mt-4 grid gap-3 text-sm">
                    <a class="transition hover:text-white" href="{{ route('storefront.catalog') }}">Sewa Harian</a>
                    <a class="transition hover:text-white" href="{{ route('storefront.catalog', ['seats' => 7]) }}">Sewa Keluarga</a>
                    <a class="transition hover:text-white" href="{{ route('storefront.how-it-works') }}">Dengan Sopir</a>
                    <a class="transition hover:text-white" href="{{ route('corporate') }}">Corporate</a>
                </div>
            </div>
            <div>
                <h2 class="text-xs font-extrabold uppercase tracking-[0.16em] text-white">Perusahaan</h2>
                <div class="mt-4 grid gap-3 text-sm">
                    <a class="transition hover:text-white" href="{{ route('about') }}">Tentang Kami</a>
                    <a class="transition hover:text-white" href="{{ route('blog.index') }}">Blog</a>
                    <a class="transition hover:text-white" href="{{ route('contact.show') }}">Kontak</a>
                    <a class="transition hover:text-white" href="{{ route('faq.index') }}">FAQ</a>
                </div>
            </div>
            <div>
                <h2 class="text-xs font-extrabold uppercase tracking-[0.16em] text-white">Akun</h2>
                <div class="mt-4 grid gap-3 text-sm">
                    <a class="transition hover:text-white" href="{{ route('portal.login') }}">Portal Pelanggan</a>
                    <a class="transition hover:text-white" href="{{ route('booking.index') }}">Booking Saya</a>
                    <a class="transition hover:text-white" href="{{ route('filament.admin.auth.login') }}">Login Admin</a>
                </div>
            </div>
        </div>
        <div class="mx-auto mt-12 max-w-7xl border-t border-white/10 px-5 pt-7 text-xs lg:px-8">
            &copy; {{ date('Y') }} {{ $brand['name'] }}. {{ $brand['copyright'] }}
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var observer = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) entry.target.classList.add('visible');
                });
            }, { threshold: 0.1 });
            document.querySelectorAll('.reveal').forEach(function (el) { observer.observe(el); });
        });
    </script>
    @stack('scripts')
</body>
</html>

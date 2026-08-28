<!DOCTYPE html>
@php($brand = app(\App\Services\WhitelabelService::class)->viewData())
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pageTitle ?? $brand['name'] }}</title>
    <meta name="description" content="{{ $seoDescription ?? $brand['tagline'] }}">
    <link rel="icon" href="{{ $brand['favicon'] }}">
    @if(isset($seoCanonical))<link rel="canonical" href="{{ $seoCanonical }}">@endif
    @if(isset($seoJsonLd))<script type="application/ld+json">{!! $seoJsonLd !!}</script>@endif
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --brand-primary: {{ $brand['primaryColor'] }}; }
        body { font-family: '{{ $brand['font'] }}', sans-serif; }
        @keyframes fadeSlideUp { 0%{transform:translateY(30px);opacity:0} 100%{transform:translateY(0);opacity:1} }
        @keyframes floatSlow { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-12px)} }
        .animate-float-slow { animation:floatSlow 5s ease-in-out infinite }
        .reveal { opacity:0;transform:translateY(30px);transition:opacity .7s,transform .7s cubic-bezier(.16,1,.3,1) }
        .reveal.visible { opacity:1;transform:translateY(0) }
        .card-lift { transition:transform .35s,box-shadow .35s }
        .card-lift:hover { transform:translateY(-6px);box-shadow:0 24px 48px -12px rgba(0,0,0,.18) }
        @media (prefers-reduced-motion:reduce) { *,*::before,*::after { animation-duration:0.01ms!important;transition-duration:0.01ms!important; } }
    </style>
    @stack('head')
</head>
<body class="bg-white text-stone-900 min-h-screen flex flex-col" x-data="{ mobileOpen: false }">
    <header class="sticky top-0 z-50 bg-white/80 backdrop-blur-md border-b border-stone-200">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <a href="/" class="flex items-center gap-2 font-bold text-xl text-indigo-600">
                @if($brand['logo'])<img src="{{ $brand['logo'] }}" alt="{{ $brand['name'] }}" class="h-10 max-w-44 object-contain">@else<span class="grid h-9 w-9 place-items-center rounded-lg text-sm text-white" style="background:var(--brand-primary)">{{ $brand['initials'] }}</span> {{ $brand['name'] }}@endif
            </a>

            {{-- Desktop nav --}}
            <div class="hidden md:flex items-center gap-8 text-sm font-medium text-stone-600">
                <a href="/" class="hover:text-indigo-600 transition">Beranda</a>
                <a href="/blog" class="hover:text-indigo-600 transition">Blog</a>
                <a href="/docs" class="hover:text-indigo-600 transition">Dokumentasi</a>
                <a href="/contact" class="hover:text-indigo-600 transition">Kontak</a>
                <a href="/admin" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition">Masuk Admin</a>
            </div>

            {{-- Mobile hamburger --}}
            <button @click="mobileOpen = !mobileOpen" class="md:hidden p-2 rounded-lg hover:bg-stone-100 transition" aria-label="Toggle menu">
                <svg x-show="!mobileOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                <svg x-show="mobileOpen" x-cloak class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </nav>

        {{-- Mobile menu --}}
        <div x-show="mobileOpen" x-cloak x-transition class="md:hidden border-t border-stone-200 bg-white px-4 py-4 space-y-3">
            <a href="/" class="block py-2 text-sm font-medium text-stone-700 hover:text-indigo-600">Beranda</a>
            <a href="/blog" class="block py-2 text-sm font-medium text-stone-700 hover:text-indigo-600">Blog</a>
            <a href="/docs" class="block py-2 text-sm font-medium text-stone-700 hover:text-indigo-600">Dokumentasi</a>
            <a href="/contact" class="block py-2 text-sm font-medium text-stone-700 hover:text-indigo-600">Kontak</a>
            <a href="/admin" class="block text-center bg-indigo-600 text-white px-4 py-2.5 rounded-lg hover:bg-indigo-700 transition text-sm font-semibold">Masuk Admin</a>
        </div>
    </header>

    <main class="flex-1">
        @yield('content')
    </main>

    <footer class="bg-stone-900 text-stone-400 py-12 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-sm">
            <p>&copy; {{ date('Y') }} {{ $brand['name'] }}. {{ $brand['copyright'] }} @if($brand['showPoweredBy']) · Powered by RentalMobil @endif</p>
        </div>
    </footer>

    {{-- WhatsApp CTA --}}
    <x-purchase-cta />

    {{-- Scroll reveal --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                    }
                });
            }, { threshold: 0.1 });
            document.querySelectorAll('.reveal').forEach(function(el) { observer.observe(el); });
        });
    </script>
    @stack('scripts')
</body>
</html>

@php($brand = app(\App\Services\WhitelabelService::class)->viewData())
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? config('app.name') }}</title>
    <meta name="description" content="{{ $description ?? '' }}">
    @if($canonical ?? null)
    <link rel="canonical" href="{{ $canonical }}">
    @endif
    <meta property="og:title" content="{{ $title ?? config('app.name') }}">
    <meta property="og:description" content="{{ $description ?? '' }}">
    <meta property="og:type" content="website">
    @if($jsonLd ?? null)
    <script type="application/ld+json">{!! $jsonLd !!}</script>
    @endif
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
                            50: '#eff6ff', 100: '#dbeafe', 200: '#bfdbfe', 300: '#93c5fd',
                            400: '#60a5fa', 500: '#3b82f6', 600: '#2563eb', 700: '#1d4ed8',
                            800: '#1e40af', 900: '#1e3a5f',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .reveal { opacity: 0; transform: translateY(20px); transition: opacity .6s, transform .6s cubic-bezier(.16,1,.3,1); }
        .reveal.visible { opacity: 1; transform: translateY(0); }
        @media (prefers-reduced-motion: reduce) { .reveal { transition-duration: 0.01ms !important; } }
    </style>
    @stack('head')
</head>
<body class="font-sans bg-stone-50 text-stone-800 antialiased">

    <header class="sticky top-0 z-50 bg-white/80 backdrop-blur-xl border-b border-stone-200/60">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-14">
            <a href="{{ route('home') }}" class="flex items-center gap-2">
                @if($brand['logo'])<img src="{{ $brand['logo'] }}" alt="{{ $brand['name'] }}" class="h-9 max-w-40 object-contain">@else<span class="grid h-8 w-8 place-items-center rounded-lg bg-brand-600 text-xs text-white">{{ $brand['initials'] }}</span><span class="font-bold text-lg text-stone-900">{{ $brand['name'] }}</span>@endif
            </a>
            <nav class="hidden md:flex items-center gap-6 text-sm font-medium">
                <a href="{{ route('home') }}" class="text-stone-500 hover:text-brand-600 transition-colors">Beranda</a>
                <a href="/sewa-mobil" class="text-stone-500 hover:text-brand-600 transition-colors">Sewa Mobil</a>
                <a href="/blog" class="text-stone-500 hover:text-brand-600 transition-colors">Blog</a>
                <a href="/faq" class="text-stone-500 hover:text-brand-600 transition-colors">FAQ</a>
                <a href="/contact" class="text-stone-500 hover:text-brand-600 transition-colors">Kontak</a>
                <a href="/docs" class="text-stone-500 hover:text-brand-600 transition-colors">Dokumentasi</a>
            </nav>
                <div class="flex items-center gap-2"><a href="/admin/login" class="hidden px-3 py-2 text-sm font-semibold text-stone-600 sm:inline-flex">Masuk Admin</a><a href="{{ route('portal.login') }}" class="px-4 py-2 bg-brand-600 text-white text-sm font-semibold rounded-lg hover:bg-brand-700 transition-all">Booking saya</a></div>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    <footer class="bg-stone-950 text-stone-400 py-12 mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-8 mb-8">
                <div>
                    <div class="flex items-center gap-2 mb-3">
                        <span class="grid h-8 w-8 place-items-center rounded-lg bg-brand-600 text-xs font-black text-white">{{ $brand['initials'] }}</span>
                        <span class="font-bold text-lg text-white">{{ $brand['name'] }}</span>
                    </div>
                    <p class="text-sm">{{ $brand['tagline'] }}</p>
                </div>
                <div>
                    <h4 class="font-semibold text-white text-sm uppercase tracking-wider mb-3">Navigasi</h4>
                    <ul class="space-y-1.5 text-sm">
                        <li><a href="{{ route('home') }}" class="hover:text-white transition-colors">Beranda</a></li>
                        <li><a href="/sewa-mobil" class="hover:text-white transition-colors">Sewa Mobil</a></li>
                        <li><a href="/blog" class="hover:text-white transition-colors">Blog</a></li>
                        <li><a href="/faq" class="hover:text-white transition-colors">FAQ</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold text-white text-sm uppercase tracking-wider mb-3">Lainnya</h4>
                    <ul class="space-y-1.5 text-sm">
                        <li><a href="/contact" class="hover:text-white transition-colors">Kontak</a></li>
                        <li><a href="/docs" class="hover:text-white transition-colors">Dokumentasi</a></li>
                        <li><a href="/sitemap.xml" class="hover:text-white transition-colors">Sitemap</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold text-white text-sm uppercase tracking-wider mb-3">Kontak</h4>
                    <ul class="space-y-1.5 text-sm">
                        <li>{{ $brand['phone'] }}</li>
                        <li>{{ $brand['email'] }}</li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-stone-800 pt-6 flex flex-col sm:flex-row justify-between items-center gap-3">
                <p class="text-xs">&copy; {{ date('Y') }} {{ $brand['name'] }}. {{ $brand['copyright'] }}</p>
                @if($brand['showPoweredBy'])<p class="text-xs text-stone-600">Powered by Laravel</p>@endif
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.reveal').forEach(el => {
                new IntersectionObserver(([e]) => { if (e.isIntersecting) e.target.classList.add('visible'); }, { threshold: 0.1 }).observe(el);
            });
        });
    </script>
    @stack('scripts')
</body>
</html>

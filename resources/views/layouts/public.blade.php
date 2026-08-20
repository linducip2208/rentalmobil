<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pageTitle ?? config('app.name') }}</title>
    <meta name="description" content="{{ $seoDescription ?? 'Blog RentalMobil - Tips dan panduan seputar rental mobil' }}">
    @if(isset($seoCanonical))<link rel="canonical" href="{{ $seoCanonical }}">@endif
    @if(isset($seoJsonLd))<script type="application/ld+json">{!! $seoJsonLd !!}</script>@endif
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-white text-stone-900 min-h-screen flex flex-col">
    <header class="sticky top-0 z-50 bg-white/80 backdrop-blur-md border-b border-stone-200">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <a href="/" class="flex items-center gap-2 font-bold text-xl text-indigo-600">
                <span class="text-2xl">🚗</span> RentalMobil
            </a>
            <div class="hidden md:flex items-center gap-8 text-sm font-medium text-stone-600">
                <a href="/" class="hover:text-indigo-600 transition">Beranda</a>
                <a href="/blog" class="hover:text-indigo-600 transition">Blog</a>
                <a href="/docs" class="hover:text-indigo-600 transition">Dokumentasi</a>
                <a href="/contact" class="hover:text-indigo-600 transition">Kontak</a>
                <a href="/admin" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition">Masuk Admin</a>
            </div>
        </nav>
    </header>
    <main class="flex-1">
        @yield('content')
    </main>
    <footer class="bg-stone-900 text-stone-400 py-12 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-sm">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>

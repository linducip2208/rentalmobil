<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hubungi Kami — RentalMobil</title>
    <meta name="description" content="Hubungi tim RentalMobil untuk pertanyaan, demo, atau kerja sama bisnis.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
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
        @media (prefers-reduced-motion: reduce) { * { animation-duration: 0.01ms !important; transition-duration: 0.01ms !important; } }
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
                <a href="/docs" class="text-sm font-medium text-stone-600 hover:text-brand-600 transition-colors">Dokumentasi</a>
                <a href="/contact" class="text-sm font-semibold text-brand-600">Kontak</a>
                <a href="/admin/login" class="px-5 py-2 bg-brand-600 text-white text-sm font-semibold rounded-lg hover:bg-brand-700 transition-all">Masuk Admin</a>
            </nav>
            <button class="md:hidden text-stone-900" x-data="{ open: false }" @click="open = !open">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
        </div>
    </header>

    <main class="pt-28 pb-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h1 class="text-3xl lg:text-4xl font-bold text-stone-900 mb-3">Hubungi Kami</h1>
                <p class="text-lg text-stone-500 max-w-xl mx-auto">Ada pertanyaan, butuh demo, atau ingin kerja sama? Kami siap membantu.</p>
            </div>

            <div class="grid lg:grid-cols-5 gap-10">
                {{-- Contact Form --}}
                <div class="lg:col-span-3">
                    @if (session('success'))
                        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-5 py-4 rounded-xl text-sm mb-6 flex items-center gap-3">
                            <span class="text-xl">✅</span>
                            <span>{{ session('success') }}</span>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="bg-red-50 border border-red-200 text-red-600 px-5 py-4 rounded-xl text-sm mb-6">
                            <ul class="list-disc list-inside space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('contact.store') }}" class="bg-white rounded-2xl border border-stone-200 p-6 lg:p-8 shadow-sm space-y-5">
                        @csrf
                        <div class="grid sm:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm font-semibold text-stone-700 mb-1.5">Nama Lengkap <span class="text-red-500">*</span></label>
                                <input type="text" name="name" value="{{ old('name') }}" required
                                       class="w-full px-4 py-3 rounded-xl border border-stone-300 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all"
                                       placeholder="Masukkan nama Anda">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-stone-700 mb-1.5">Email <span class="text-red-500">*</span></label>
                                <input type="email" name="email" value="{{ old('email') }}" required
                                       class="w-full px-4 py-3 rounded-xl border border-stone-300 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all"
                                       placeholder="email@contoh.com">
                            </div>
                        </div>
                        <div class="grid sm:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm font-semibold text-stone-700 mb-1.5">Telepon</label>
                                <input type="text" name="phone" value="{{ old('phone') }}"
                                       class="w-full px-4 py-3 rounded-xl border border-stone-300 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all"
                                       placeholder="+62 812-3456-7890">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-stone-700 mb-1.5">Subjek <span class="text-red-500">*</span></label>
                                <input type="text" name="subject" value="{{ old('subject') }}" required
                                       class="w-full px-4 py-3 rounded-xl border border-stone-300 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all"
                                       placeholder="Perihal pesan Anda">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-stone-700 mb-1.5">Pesan <span class="text-red-500">*</span></label>
                            <textarea name="message" rows="5" required
                                      class="w-full px-4 py-3 rounded-xl border border-stone-300 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all resize-y"
                                      placeholder="Tuliskan pesan Anda di sini...">{{ old('message') }}</textarea>
                        </div>
                        <button type="submit" class="w-full sm:w-auto px-8 py-3 bg-brand-600 text-white font-semibold rounded-xl hover:bg-brand-700 transition-all hover:shadow-lg hover:shadow-brand-600/25 hover:-translate-y-0.5">
                            Kirim Pesan
                        </button>
                    </form>
                </div>

                {{-- Sidebar --}}
                <div class="lg:col-span-2 space-y-6">
                    {{-- Company Info --}}
                    <div class="bg-white rounded-2xl border border-stone-200 p-6 shadow-sm">
                        <h3 class="font-bold text-stone-900 mb-4">Informasi Perusahaan</h3>
                        <div class="space-y-4 text-sm">
                            <div class="flex items-start gap-3">
                                <span class="text-xl mt-0.5">📍</span>
                                <div>
                                    <div class="font-semibold text-stone-700">Alamat</div>
                                    <div class="text-stone-500">Jl. Raya Utama No. 123<br>Jakarta Selatan, DKI Jakarta 12345</div>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <span class="text-xl mt-0.5">📞</span>
                                <div>
                                    <div class="font-semibold text-stone-700">Telepon</div>
                                    <div class="text-stone-500">+62 812-3456-7890</div>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <span class="text-xl mt-0.5">✉️</span>
                                <div>
                                    <div class="font-semibold text-stone-700">Email</div>
                                    <div class="text-stone-500">hello@rentalmobil.id</div>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <span class="text-xl mt-0.5">🕐</span>
                                <div>
                                    <div class="font-semibold text-stone-700">Jam Operasional</div>
                                    <div class="text-stone-500">Senin - Sabtu: 08:00 - 17:00 WIB<br>Minggu: Libur</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Map Placeholder --}}
                    <div class="bg-white rounded-2xl border border-stone-200 overflow-hidden shadow-sm">
                        <div class="bg-stone-200 aspect-[4/3] flex items-center justify-center">
                            <div class="text-center text-stone-400">
                                <div class="text-4xl mb-2">🗺️</div>
                                <div class="text-sm font-medium">Peta Lokasi</div>
                                <div class="text-xs mt-1">Google Maps embed di sini</div>
                            </div>
                        </div>
                    </div>

                    {{-- Quick Contact --}}
                    <div class="bg-gradient-to-r from-brand-600 to-indigo-600 rounded-2xl p-6 text-white shadow-lg shadow-brand-600/20">
                        <h3 class="font-bold mb-2">Butuh Respons Cepat?</h3>
                        <p class="text-brand-100 text-sm mb-4">Hubungi kami langsung via WhatsApp untuk respons lebih cepat.</p>
                        <a href="https://wa.me/6281234567890" target="_blank" class="inline-flex items-center gap-2 bg-white text-brand-700 px-5 py-2.5 rounded-xl text-sm font-bold hover:bg-brand-50 transition-all hover:shadow-lg">
                            💬 Chat WhatsApp
                        </a>
                    </div>
                </div>
            </div>
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
</body>
</html>

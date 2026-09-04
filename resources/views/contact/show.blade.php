<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hubungi Kami — RentalMobil</title>
    <meta name="description" content="Hubungi tim RentalMobil untuk pertanyaan, demo, atau kerja sama bisnis.">
    @php($brand = app(\App\Services\WhitelabelService::class)->viewData())
    <link rel="icon" href="{{ $brand['favicon'] }}">
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
                @if($brand['logo'])
                    <img src="{{ $brand['logo'] }}" alt="{{ $brand['name'] }}" class="h-9 max-w-40 object-contain">
                @else
                    <span class="grid h-9 w-9 place-items-center rounded-xl bg-brand-600 text-sm font-black text-white">{{ $brand['initials'] }}</span>
                    <span class="font-bold text-xl text-stone-900">{{ $brand['name'] }}</span>
                @endif
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
                            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
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
                                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-brand-50 text-brand-600">
                                    <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                </span>
                                <div>
                                    <div class="font-semibold text-stone-700">Alamat</div>
                                    <div class="text-stone-500">{{ $brand['address'] }}</div>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-brand-50 text-brand-600">
                                    <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                </span>
                                <div>
                                    <div class="font-semibold text-stone-700">Telepon</div>
                                    <div class="text-stone-500">{{ $brand['phone'] }}</div>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-brand-50 text-brand-600">
                                    <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                </span>
                                <div>
                                    <div class="font-semibold text-stone-700">Email</div>
                                    <div class="text-stone-500">{{ $brand['email'] }}</div>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-brand-50 text-brand-600">
                                    <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </span>
                                <div>
                                    <div class="font-semibold text-stone-700">Jam Operasional</div>
                                    <div class="text-stone-500">Senin - Sabtu: 08:00 - 17:00 WIB<br>Minggu: Libur</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Quick Contact --}}
                    <div class="bg-gradient-to-r from-brand-600 to-indigo-600 rounded-2xl p-6 text-white shadow-lg shadow-brand-600/20">
                        <h3 class="font-bold mb-2">Butuh Respons Cepat?</h3>
                        <p class="text-brand-100 text-sm mb-4">Hubungi kami langsung via WhatsApp untuk respons lebih cepat.</p>
                        <a href="https://wa.me/{{ $brand['whatsapp'] }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 bg-white text-brand-700 px-5 py-2.5 rounded-xl text-sm font-bold hover:bg-brand-50 transition-all hover:shadow-lg">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                            Chat WhatsApp
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
                @if($brand['logo'])
                    <img src="{{ $brand['logo'] }}" alt="{{ $brand['name'] }}" class="h-8 max-w-32 object-contain">
                @else
                    <span class="grid h-8 w-8 place-items-center rounded-lg bg-brand-600 text-xs font-black text-white">{{ $brand['initials'] }}</span>
                    <span class="font-bold text-white">{{ $brand['name'] }}</span>
                @endif
            </div>
            <p class="text-xs">&copy; {{ date('Y') }} {{ $brand['name'] }}. {{ $brand['copyright'] }}</p>
        </div>
    </footer>
</body>
</html>

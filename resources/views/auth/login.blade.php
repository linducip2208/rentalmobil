<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk — {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:wght@700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-display { font-family: 'Playfair Display', serif; }
        @keyframes floatCircle { 0%,100%{transform:translateY(0) scale(1)} 50%{transform:translateY(-20px) scale(1.05)} }
        .animate-float { animation: floatCircle 6s ease-in-out infinite; }
        .animate-float-delay { animation: floatCircle 6s ease-in-out 2s infinite; }
    </style>
</head>
<body class="bg-stone-50 min-h-screen">
    <div class="min-h-screen grid lg:grid-cols-2 gap-0">
        {{-- Left: Hero Brand Panel --}}
        <div class="hidden lg:flex relative bg-gradient-to-br from-indigo-600 via-indigo-700 to-stone-900 p-12 flex-col justify-between overflow-hidden">
            {{-- Decorative gradient circles --}}
            <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(circle at 20% 80%, rgba(139,92,246,0.4) 0%, transparent 50%), radial-gradient(circle at 80% 20%, rgba(99,102,241,0.3) 0%, transparent 50%)"></div>
            {{-- Large decorative emoji --}}
            <div class="absolute -bottom-20 -right-20 text-[18rem] opacity-10 animate-float">🚗</div>
            <div class="absolute -top-10 -left-10 text-[12rem] opacity-5 animate-float-delay">🏎️</div>

            {{-- Logo + brand name --}}
            <div class="relative">
                <a href="/" class="flex items-center gap-3 text-white">
                    <span class="text-4xl">🚗</span>
                    <span class="font-display font-bold text-3xl">RentalMobil</span>
                </a>
            </div>

            {{-- Tagline + benefit cards --}}
            <div class="relative text-white">
                <h2 class="font-display text-5xl font-bold leading-tight mb-4">Sistem Rental Mobil<br>Profesional</h2>
                <p class="text-indigo-200 text-lg leading-relaxed mb-8 max-w-md">
                    Kelola armada, booking, pembayaran, dan laporan dalam satu platform terintegrasi.
                </p>
                <div class="grid grid-cols-3 gap-4 max-w-md">
                    <div class="bg-white/10 backdrop-blur-sm rounded-xl p-3 text-center">
                        <div class="text-2xl mb-1">📊</div>
                        <div class="text-xs text-indigo-200 font-medium">Real-time Dashboard</div>
                    </div>
                    <div class="bg-white/10 backdrop-blur-sm rounded-xl p-3 text-center">
                        <div class="text-2xl mb-1">🔒</div>
                        <div class="text-xs text-indigo-200 font-medium">Anti-Fraud System</div>
                    </div>
                    <div class="bg-white/10 backdrop-blur-sm rounded-xl p-3 text-center">
                        <div class="text-2xl mb-1">📱</div>
                        <div class="text-xs text-indigo-200 font-medium">Mobile Ready</div>
                    </div>
                </div>
            </div>

            {{-- Copyright --}}
            <div class="relative text-indigo-300/70 text-xs">
                &copy; {{ date('Y') }} {{ config('app.name') }} &middot; Powered by Laravel + Filament
            </div>
        </div>

        {{-- Right: Login Form --}}
        <div class="flex items-center justify-center p-8 lg:p-16">
            <div class="w-full max-w-md">
                {{-- Mobile logo --}}
                <div class="lg:hidden flex items-center gap-2 mb-8">
                    <span class="text-3xl">🚗</span>
                    <span class="font-display font-bold text-2xl text-stone-900">RentalMobil</span>
                </div>

                <h1 class="font-display text-4xl font-bold text-stone-900 mb-2">Masuk</h1>
                <p class="text-stone-500 mb-8">
                    Belum punya akun? <a href="/" class="text-indigo-600 font-semibold hover:underline">Kembali ke beranda</a>
                </p>

                {{-- Error alert --}}
                @if ($errors->any())
                    <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl p-4 mb-6 text-sm">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Login form --}}
                <form method="POST" action="{{ url('/admin/login') }}">
                    @csrf
                    <div class="space-y-5">
                        <div>
                            <label for="email" class="block text-sm font-semibold text-stone-700 mb-1.5">Email</label>
                            <input
                                type="email"
                                name="email"
                                id="email"
                                value="{{ old('email') }}"
                                required
                                autofocus
                                class="w-full px-4 py-3 rounded-xl border border-stone-300 bg-white text-stone-900 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition"
                                placeholder="nama@email.com"
                            >
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-semibold text-stone-700 mb-1.5">Password</label>
                            <input
                                type="password"
                                name="password"
                                id="password"
                                required
                                class="w-full px-4 py-3 rounded-xl border border-stone-300 bg-white text-stone-900 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition"
                                placeholder="Masukkan password"
                            >
                        </div>

                        <div class="flex items-center justify-between">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="remember" class="w-4 h-4 rounded border-stone-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm text-stone-600">Ingat saya</span>
                            </label>
                        </div>

                        <button
                            type="submit"
                            class="w-full py-3 px-6 rounded-xl text-white font-semibold text-sm
                                   bg-gradient-to-r from-indigo-600 to-violet-600
                                   hover:from-indigo-700 hover:to-violet-700
                                   shadow-lg shadow-indigo-500/25
                                   hover:shadow-xl hover:shadow-indigo-500/30
                                   transition-all duration-200 hover:-translate-y-0.5"
                        >
                            Masuk
                        </button>
                    </div>
                </form>

                {{-- Divider --}}
                <div class="flex items-center gap-3 my-8">
                    <div class="flex-1 h-px bg-stone-200"></div>
                    <span class="text-xs text-stone-400 font-medium">atau</span>
                    <div class="flex-1 h-px bg-stone-200"></div>
                </div>

                {{-- Demo Login --}}
                <div class="bg-stone-50 border border-stone-200 rounded-xl p-4 text-sm">
                    <div class="font-semibold text-stone-800 mb-2">🧪 Demo Login</div>
                    <div class="space-y-1.5 text-stone-600 text-xs font-mono">
                        <div><span class="font-bold text-stone-800">Owner:</span> admin@rentalmobil.test / password</div>
                        <div><span class="font-bold text-stone-800">Manager:</span> manager@rentalmobil.test / password</div>
                        <div><span class="font-bold text-stone-800">Admin:</span> admin2@rentalmobil.test / password</div>
                        <div><span class="font-bold text-stone-800">Kasir:</span> kasir@rentalmobil.test / password</div>
                        <div><span class="font-bold text-stone-800">Driver:</span> driver@rentalmobil.test / password</div>
                    </div>
                </div>

                {{-- Footer --}}
                <p class="text-center text-xs text-stone-400 mt-8">
                    &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                </p>
            </div>
        </div>
    </div>
</body>
</html>

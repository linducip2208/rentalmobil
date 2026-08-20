<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk — {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:wght@700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        display: ['Playfair Display', 'serif'],
                        sans: ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
</head>
<body class="font-sans bg-stone-50 antialiased">

    <div class="min-h-screen grid lg:grid-cols-2 gap-0">

        {{-- Left: Hero Brand Panel --}}
        <div class="hidden lg:flex relative bg-gradient-to-br from-blue-600 via-blue-700 to-slate-900 p-12 flex-col justify-between overflow-hidden">
            <div class="absolute inset-0 opacity-30" style="background-image: radial-gradient(circle at 20% 80%, rgba(96,165,250,0.5) 0%, transparent 50%), radial-gradient(circle at 80% 20%, rgba(147,197,253,0.3) 0%, transparent 50%)"></div>
            <div class="absolute -bottom-20 -right-20 text-[20rem] opacity-10 select-none">🚗</div>

            <div class="relative">
                <a href="{{ route('home') }}" class="flex items-center gap-2 text-white">
                    <span class="text-3xl">🚗</span>
                    <span class="font-display font-bold text-2xl">RentalMobil</span>
                </a>
            </div>

            <div class="relative text-white">
                <h2 class="font-display text-5xl font-bold leading-tight mb-4">Sewa Mobil<br>Mudah &<br>Terpercaya</h2>
                <p class="text-blue-100 text-lg leading-relaxed mb-8 max-w-md">Kelola armada, booking, dan operasional rental mobil Anda dalam satu platform terintegrasi.</p>
                <div class="grid grid-cols-3 gap-4 max-w-md">
                    <div class="bg-white/10 backdrop-blur rounded-xl p-3 text-center">
                        <div class="text-2xl mb-1">🚗</div>
                        <div class="text-xs text-blue-100 font-medium">250+ Unit</div>
                    </div>
                    <div class="bg-white/10 backdrop-blur rounded-xl p-3 text-center">
                        <div class="text-2xl mb-1">📋</div>
                        <div class="text-xs text-blue-100 font-medium">Booking 24/7</div>
                    </div>
                    <div class="bg-white/10 backdrop-blur rounded-xl p-3 text-center">
                        <div class="text-2xl mb-1">💰</div>
                        <div class="text-xs text-blue-100 font-medium">Harga Transparan</div>
                    </div>
                </div>
            </div>

            <div class="relative text-blue-200/70 text-xs">
                &copy; {{ date('Y') }} {{ config('app.name') }} &middot; Powered by Laravel
            </div>
        </div>

        {{-- Right: Login Form --}}
        <div class="flex items-center justify-center p-8 lg:p-16">
            <div class="w-full max-w-md">
                {{-- Mobile Logo --}}
                <div class="lg:hidden flex items-center gap-2 mb-8">
                    <span class="text-2xl">🚗</span>
                    <span class="font-display font-bold text-xl text-stone-900">RentalMobil</span>
                </div>

                <h1 class="font-display text-4xl font-bold text-stone-900 mb-2">Masuk</h1>
                <p class="text-stone-500 mb-8">
                    Belum punya akun?
                    <a href="{{ route('portal.login') }}" class="text-blue-600 font-semibold hover:underline">Daftar gratis</a>
                </p>

                @if($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl px-4 py-3 mb-6">
                    {{ $errors->first() }}
                </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="mb-5">
                        <label for="email" class="block text-sm font-semibold text-stone-700 mb-1.5">Email</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            class="w-full px-4 py-3 rounded-xl border border-stone-300 text-stone-900 text-sm focus:ring-2 focus:ring-blue-100 focus:border-blue-500 outline-none transition-all"
                            placeholder="email@contoh.com"
                        >
                    </div>

                    <div class="mb-5">
                        <label for="password" class="block text-sm font-semibold text-stone-700 mb-1.5">Password</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            required
                            class="w-full px-4 py-3 rounded-xl border border-stone-300 text-stone-900 text-sm focus:ring-2 focus:ring-blue-100 focus:border-blue-500 outline-none transition-all"
                            placeholder="Masukkan password"
                        >
                    </div>

                    <div class="flex items-center justify-between mb-8">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="remember" class="rounded border-stone-300 text-blue-600 focus:ring-blue-500">
                            <span class="text-sm text-stone-600">Ingat saya</span>
                        </label>
                        <a href="#" class="text-sm text-blue-600 font-medium hover:underline">Lupa password?</a>
                    </div>

                    <button type="submit" class="w-full py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-bold text-sm rounded-xl hover:from-blue-700 hover:to-blue-800 transition-all shadow-lg shadow-blue-600/25 hover:shadow-xl hover:shadow-blue-600/30 hover:-translate-y-0.5">
                        Masuk
                    </button>
                </form>

                <div class="flex items-center gap-3 my-6">
                    <div class="flex-1 h-px bg-stone-200"></div>
                    <span class="text-xs text-stone-400 font-medium uppercase tracking-wider">atau</span>
                    <div class="flex-1 h-px bg-stone-200"></div>
                </div>

                <a href="{{ route('portal.login') }}" class="block w-full text-center py-3 border-2 border-stone-200 text-stone-600 font-semibold text-sm rounded-xl hover:bg-stone-50 hover:border-stone-300 transition-all">
                    Masuk sebagai Pelanggan
                </a>

                {{-- Demo Login --}}
                <div class="bg-stone-50 border border-stone-200 rounded-xl p-4 mt-6 text-sm">
                    <div class="font-semibold text-stone-800 mb-2">🧪 Demo Login</div>
                    <div class="space-y-1 text-stone-600 text-xs font-mono">
                        <div><span class="font-bold">Owner:</span> owner@rentalmobil.test / password</div>
                        <div><span class="font-bold">Manager:</span> manager@rentalmobil.test / password</div>
                        <div><span class="font-bold">Admin:</span> admin@rentalmobil.test / password</div>
                        <div><span class="font-bold">Kasir:</span> kasir@rentalmobil.test / password</div>
                        <div><span class="font-bold">Driver:</span> driver@rentalmobil.test / password</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>

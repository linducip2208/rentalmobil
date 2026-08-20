<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk - {{ config('app.name', 'RentalMobil') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-stone-50">
<div class="min-h-screen grid lg:grid-cols-2 gap-0">

    <div class="hidden lg:flex relative bg-gradient-to-br from-blue-600 via-blue-700 to-slate-900 p-12 flex-col justify-between overflow-hidden">
        <div class="absolute inset-0 opacity-20"
             style="background-image: radial-gradient(circle at 20% 50%, rgba(255,255,255,0.3) 0%, transparent 50%), radial-gradient(circle at 80% 20%, rgba(99,102,241,0.4) 0%, transparent 40%)"></div>
        <div class="absolute -bottom-20 -right-20 text-[18rem] opacity-10 select-none">&#128663;</div>

        <div class="relative">
            <a href="/" class="flex items-center gap-2 text-white">
                <span class="text-3xl">&#128663;</span>
                <span class="font-bold text-2xl">{{ config('app.name', 'RentalMobil') }}</span>
            </a>
        </div>

        <div class="relative text-white">
            <h2 class="text-5xl font-bold leading-tight mb-4">Sewa Mobil<br>Mudah &amp; Cepat</h2>
            <p class="text-blue-100 text-lg leading-relaxed mb-8 max-w-md">Akses portal customer untuk mengelola booking, pesanan, dan pembayaran Anda dalam satu tempat.</p>
            <div class="grid grid-cols-3 gap-4 max-w-md">
                <div class="bg-white/10 backdrop-blur rounded-xl p-3 text-center">
                    <div class="text-2xl mb-1">&#128663;</div>
                    <div class="text-xs font-medium text-blue-100">Pilihan Mobil</div>
                </div>
                <div class="bg-white/10 backdrop-blur rounded-xl p-3 text-center">
                    <div class="text-2xl mb-1">&#128197;</div>
                    <div class="text-xs font-medium text-blue-100">Booking Online</div>
                </div>
                <div class="bg-white/10 backdrop-blur rounded-xl p-3 text-center">
                    <div class="text-2xl mb-1">&#128176;</div>
                    <div class="text-xs font-medium text-blue-100">Bayar Mudah</div>
                </div>
            </div>
        </div>

        <div class="relative text-blue-200/70 text-xs">&copy; {{ date('Y') }} {{ config('app.name', 'RentalMobil') }}</div>
    </div>

    <div class="flex items-center justify-center p-8 lg:p-16">
        <div class="w-full max-w-md">
            <a href="/" class="flex items-center gap-2 mb-8 lg:hidden">
                <span class="text-2xl">&#128663;</span>
                <span class="font-bold text-xl text-blue-700">{{ config('app.name', 'RentalMobil') }}</span>
            </a>

            <h1 class="text-4xl font-bold text-stone-900 mb-2">Masuk</h1>
            <p class="text-stone-500 mb-8">Belum punya akun? <span class="text-blue-600 font-semibold">Hubungi kami</span></p>

            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-600 text-sm px-4 py-3 rounded-xl mb-6">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('portal.login.post') }}" class="space-y-5">
                @csrf
                <div>
                    <label for="email" class="block text-sm font-medium text-stone-700 mb-1.5">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                           class="w-full px-4 py-3 rounded-xl border border-stone-300 text-stone-900 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-stone-700 mb-1.5">Password</label>
                    <input type="password" id="password" name="password" required
                           class="w-full px-4 py-3 rounded-xl border border-stone-300 text-stone-900 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition">
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="remember" class="rounded border-stone-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-sm text-stone-600">Ingat saya</span>
                    </label>
                </div>

                <button type="submit"
                        class="w-full bg-gradient-to-r from-blue-600 to-blue-700 text-white py-3 rounded-xl text-sm font-semibold hover:from-blue-700 hover:to-blue-800 transition shadow-lg shadow-blue-500/25">
                    Masuk
                </button>
            </form>

            <div class="mt-8 pt-6 border-t border-stone-200">
                <div class="bg-stone-50 border border-stone-200 rounded-xl p-4 text-sm">
                    <div class="font-semibold text-stone-800 mb-2">&#129513; Demo Login</div>
                    <div class="space-y-1 text-stone-600 text-xs font-mono">
                        <div><span class="font-bold">Customer:</span> customer@rentalmobil.test / password</div>
                        <div><span class="font-bold">Admin:</span> admin@rentalmobil.test / password</div>
                        <div><span class="font-bold">Manager:</span> manager@rentalmobil.test / password</div>
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>
</body>
</html>

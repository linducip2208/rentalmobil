@extends('portal.layout')
@section('title','Masuk Portal')
@section('content')
<div class="grid min-h-[calc(100vh-8rem)] overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl shadow-slate-200/70 lg:grid-cols-[1.05fr_.95fr]">
    <section class="relative hidden overflow-hidden bg-slate-950 p-12 text-white lg:flex lg:flex-col lg:justify-between">
        <div class="absolute inset-x-0 top-1/2 h-px bg-gradient-to-r from-transparent via-amber-400 to-transparent"></div>
        <div class="relative font-mono text-xs uppercase tracking-[.25em] text-blue-300">Perjalanan Anda, dalam kendali</div>
        <div class="relative"><p class="mb-5 text-sm font-semibold text-amber-300">PORTAL SELF-SERVICE</p><h1 class="max-w-lg text-5xl font-black leading-[1.04]">Sewa mobil tanpa menunggu balasan admin.</h1><p class="mt-6 max-w-md text-lg leading-relaxed text-slate-300">Pantau pesanan, invoice, pembayaran, dan jadwal pengembalian dari satu tempat.</p></div>
        <div class="relative grid grid-cols-3 gap-3 text-xs"><div class="rounded-xl border border-white/10 bg-white/5 p-4">Status real-time</div><div class="rounded-xl border border-white/10 bg-white/5 p-4">Invoice rapi</div><div class="rounded-xl border border-white/10 bg-white/5 p-4">Riwayat aman</div></div>
    </section>
    <section class="flex items-center p-6 sm:p-12 lg:p-16"><div class="mx-auto w-full max-w-md"><a href="/" class="text-sm font-semibold text-blue-600">← Kembali ke beranda</a><h2 class="mt-8 text-4xl font-black tracking-tight">Masuk</h2><p class="mt-2 text-slate-500">Gunakan akun pelanggan yang terdaftar.</p>
        @if($errors->any())<div class="mt-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">{{ $errors->first() }}</div>@endif
        <form method="post" action="{{ route('portal.login.store') }}" class="mt-8 space-y-5">@csrf
            <label class="block text-sm font-bold">Email<input name="email" type="email" value="{{ old('email') }}" required autofocus class="mt-2 min-h-12 w-full rounded-xl border border-slate-300 px-4 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100"></label>
            <label class="block text-sm font-bold">Password<input name="password" type="password" required class="mt-2 min-h-12 w-full rounded-xl border border-slate-300 px-4 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100"></label>
            <label class="flex items-center gap-3 text-sm text-slate-600"><input name="remember" type="checkbox" value="1" class="h-5 w-5 rounded"> Ingat saya</label>
            <button class="min-h-12 w-full rounded-xl bg-blue-600 px-5 font-bold text-white shadow-lg shadow-blue-200 hover:bg-blue-700">Masuk ke portal</button>
        </form></div></section>
</div>
@endsection

<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Portal Pelanggan') — {{ config('app.name') }}</title>
    <link rel="manifest" href="/manifest.webmanifest"><meta name="theme-color" content="#1d4ed8">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root{color-scheme:light} body{font-family:Inter,ui-sans-serif,system-ui;background:#f5f7fa}
        .road-grid{background-image:linear-gradient(rgba(15,23,42,.035) 1px,transparent 1px),linear-gradient(90deg,rgba(15,23,42,.035) 1px,transparent 1px);background-size:32px 32px}
        .lift{transition:transform .25s ease,box-shadow .25s ease}.lift:hover{transform:translateY(-3px);box-shadow:0 18px 40px -24px #0f172a}
        @media(prefers-reduced-motion:reduce){*{transition-duration:.01ms!important}}
    </style>
</head>
<body class="road-grid min-h-screen text-slate-900">
<header class="sticky top-0 z-30 border-b border-slate-200 bg-white/90 backdrop-blur-xl">
    <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6">
        <a href="{{ route('portal.dashboard') }}" class="flex items-center gap-3 font-extrabold"><span class="grid h-9 w-9 place-items-center rounded-xl bg-blue-600 text-white">R</span><span>RentalMobil <small class="block font-mono text-[10px] font-medium uppercase tracking-[.18em] text-slate-400">Portal pelanggan</small></span></a>
        @auth('customer')
        <nav class="flex items-center gap-2 text-sm"><a class="hidden rounded-lg px-3 py-2 hover:bg-slate-100 sm:block" href="{{ route('portal.orders') }}">Pesanan</a><a class="hidden rounded-lg px-3 py-2 hover:bg-slate-100 sm:block" href="{{ route('portal.subscriptions') }}">Langganan</a><a class="hidden rounded-lg px-3 py-2 hover:bg-slate-100 sm:block" href="{{ route('portal.invoices') }}">Invoice</a><form method="post" action="{{ route('portal.logout') }}">@csrf<button class="min-h-10 rounded-lg border border-slate-300 px-3 font-semibold hover:bg-slate-50">Keluar</button></form></nav>
        @endauth
    </div>
</header>
<main class="mx-auto max-w-7xl px-4 py-8 sm:px-6">@yield('content')</main>
<script>if('serviceWorker' in navigator){navigator.serviceWorker.register('/service-worker.js')}</script></body></html>

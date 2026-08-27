<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $page->seoMeta?->meta_title ?: $page->title }}</title>
    <meta name="description" content="{{ $page->seoMeta?->meta_description }}">
    <meta name="robots" content="{{ $page->seoMeta?->is_indexable === false ? 'noindex' : 'index' }},{{ $page->seoMeta?->is_followable === false ? 'nofollow' : 'follow' }}">
    <link rel="canonical" href="{{ $page->seoMeta?->canonical_url ?: url()->current() }}">
    <meta property="og:title" content="{{ $page->seoMeta?->og_title ?: $page->title }}">
    <meta property="og:description" content="{{ $page->seoMeta?->og_description ?: $page->seoMeta?->meta_description }}">
    @if($page->seoMeta?->og_image)<meta property="og:image" content="{{ Storage::disk('public')->url($page->seoMeta->og_image) }}">@endif
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        .reveal{opacity:0;transform:translateY(28px);transition:.7s cubic-bezier(.16,1,.3,1)}.reveal.visible{opacity:1;transform:none}
        .card-lift{transition:transform .3s,box-shadow .3s}.card-lift:hover{transform:translateY(-6px);box-shadow:0 24px 48px -18px #0f172a55}
        @keyframes floatSlow{50%{transform:translateY(-12px)}}.float{animation:floatSlow 5s ease-in-out infinite}
        @media(prefers-reduced-motion:reduce){*,*::before,*::after{animation-duration:.01ms!important;transition-duration:.01ms!important}}
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased">
<header class="sticky top-0 z-40 border-b border-slate-200/70 bg-white/85 backdrop-blur-xl">
    <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4">
        <a href="{{ route('home') }}" class="text-xl font-extrabold text-blue-700">{{ \App\Models\SystemSetting::get('company_name', config('app.name')) }}</a>
        <div class="flex items-center gap-3"><a href="{{ route('booking.index') }}" class="rounded-xl bg-blue-600 px-4 py-2.5 font-semibold text-white">Booking Mobil</a></div>
    </div>
</header>

<main>
    @foreach($page->sections as $section)
        @php($data = $section->data ?? [])
        @switch($section->block_type)
            @case('hero')
                <section class="relative overflow-hidden bg-slate-950 px-4 py-24 text-white">
                    <div class="absolute -right-20 top-10 h-72 w-72 rounded-full bg-blue-500/20 blur-3xl float"></div>
                    <div class="reveal relative mx-auto max-w-7xl">
                        <p class="mb-4 font-semibold uppercase tracking-[.18em] text-blue-300">{{ $data['eyebrow'] ?? 'Rental Mobil Enterprise' }}</p>
                        <h1 class="max-w-4xl text-4xl font-black leading-tight md:text-6xl">{{ $data['heading'] ?? $page->title }}</h1>
                        <p class="mt-6 max-w-2xl text-lg leading-8 text-slate-300">{{ $data['description'] ?? '' }}</p>
                        <a href="{{ $data['button_url'] ?? route('booking.index') }}" class="mt-8 inline-flex min-h-11 items-center rounded-xl bg-blue-500 px-6 font-bold text-white">{{ $data['button_label'] ?? 'Cek Ketersediaan' }}</a>
                    </div>
                </section>
                @break
            @case('heading')
                <section class="reveal mx-auto max-w-7xl px-4 pt-16"><h2 class="text-3xl font-black md:text-4xl">{{ $data['text'] ?? $section->name }}</h2></section>
                @break
            @case('rich_text')
            @case('custom_html')
                <section class="reveal prose prose-slate mx-auto max-w-4xl px-4 py-10">{!! app(\App\Services\CmsBlockRenderer::class)->sanitizeHtml($data['html'] ?? '') !!}</section>
                @break
            @case('image')
                <section class="reveal mx-auto max-w-6xl px-4 py-10"><img src="{{ Storage::disk('public')->url($data['path'] ?? '') }}" alt="{{ $data['alt'] ?? '' }}" class="h-auto w-full rounded-2xl object-cover shadow-xl"></section>
                @break
            @case('vehicle_list')
                <section class="mx-auto max-w-7xl px-4 py-16"><div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">@foreach($vehicles as $vehicle)<article class="reveal card-lift rounded-2xl border border-slate-200 bg-white p-5"><h3 class="text-xl font-bold">{{ $vehicle->name }}</h3><p class="mt-1 text-sm text-slate-500">{{ $vehicle->brand?->name }} · {{ $vehicle->category?->name }}</p><p class="mt-5 text-lg font-black text-blue-700">Rp {{ number_format($vehicle->daily_rate, 0, ',', '.') }}/hari</p><a href="{{ route('pseo.vehicle-detail', $vehicle) }}" class="mt-4 inline-block font-semibold text-blue-600">Lihat kendaraan →</a></article>@endforeach</div></section>
                @break
            @case('faq')
                <section class="mx-auto max-w-4xl px-4 py-16" x-data="{open:null}"><div class="space-y-3">@foreach($faqs as $faq)<article class="reveal rounded-xl border border-slate-200 bg-white"><button type="button" class="flex min-h-12 w-full items-center justify-between p-5 text-left font-bold" @click="open=open==={{ $faq->id }}?null:{{ $faq->id }}">{{ $faq->question }}<span>+</span></button><div x-show="open==={{ $faq->id }}" x-collapse class="px-5 pb-5 text-slate-600">{{ $faq->answer }}</div></article>@endforeach</div></section>
                @break
            @case('cta')
                <section class="reveal mx-auto my-16 max-w-7xl px-4"><div class="rounded-3xl bg-blue-700 p-10 text-center text-white md:p-16"><h2 class="text-3xl font-black">{{ $data['heading'] ?? 'Siap mulai menyewa?' }}</h2><p class="mx-auto mt-4 max-w-2xl text-blue-100">{{ $data['description'] ?? '' }}</p><a href="{{ $data['button_url'] ?? route('booking.index') }}" class="mt-7 inline-flex min-h-11 items-center rounded-xl bg-white px-6 font-bold text-blue-700">{{ $data['button_label'] ?? 'Booking Sekarang' }}</a></div></section>
                @break
            @default
                @if(!empty($data['text']))<section class="reveal mx-auto max-w-7xl px-4 py-10">{{ $data['text'] }}</section>@endif
        @endswitch
    @endforeach
</main>

<footer class="bg-slate-950 px-4 py-10 text-center text-sm text-slate-400">© {{ date('Y') }} {{ \App\Models\SystemSetting::get('company_name', config('app.name')) }}</footer>
<script>document.querySelectorAll('.reveal').forEach(el=>new IntersectionObserver(([e],o)=>{if(e.isIntersecting){el.classList.add('visible');o.disconnect()}},{threshold:.12}).observe(el))</script>
</body>
</html>

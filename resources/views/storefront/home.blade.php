@extends('storefront.layout')

@php($brand = app(\App\Services\WhitelabelService::class)->viewData())

@section('title', 'Sewa Mobil Premium & Terpercaya — '.$brand['name'])

@section('content')
    <section class="relative overflow-hidden bg-fleet-950 text-white">
        <div class="hero-grid absolute inset-0" aria-hidden="true"></div>
        <div class="relative mx-auto grid max-w-7xl items-center gap-14 px-5 pb-24 pt-32 lg:grid-cols-[1.02fr_.98fr] lg:px-8 lg:pb-32 lg:pt-44">
            <div>
                <p class="mb-5 inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/5 px-4 py-1.5 text-xs font-bold uppercase tracking-[.18em] text-sky-300">Mobilitas pribadi &amp; perusahaan</p>
                <h1 class="font-display text-4xl font-extrabold leading-[1.05] tracking-[-.04em] sm:text-5xl lg:text-6xl">Temukan Mobil yang Tepat untuk Setiap Perjalanan</h1>
                <p class="mt-6 max-w-xl text-lg leading-8 text-slate-300">Sewa mobil harian, mingguan, atau bulanan dengan proses cepat, harga transparan, dan armada terawat. Lepas kunci atau dengan sopir.</p>
                <dl class="mt-9 flex flex-wrap gap-x-10 gap-y-4">
                    @if(($stats['vehicles'] ?? null))
                        <div><dt class="text-xs font-bold uppercase tracking-[.14em] text-slate-400">Armada</dt><dd class="mt-1 font-display text-2xl font-extrabold">{{ $stats['vehicles'] }} kendaraan</dd></div>
                    @endif
                    @if(($stats['locations'] ?? null))
                        <div><dt class="text-xs font-bold uppercase tracking-[.14em] text-slate-400">Cabang</dt><dd class="mt-1 font-display text-2xl font-extrabold">{{ $stats['locations'] }} kota</dd></div>
                    @endif
                    <div><dt class="text-xs font-bold uppercase tracking-[.14em] text-slate-400">Layanan</dt><dd class="mt-1 font-display text-2xl font-extrabold">7 hari/minggu</dd></div>
                </dl>
            </div>
            <div class="relative min-h-[300px] overflow-hidden rounded-[2rem] border border-white/10 bg-gradient-to-br from-slate-800 to-fleet-900 shadow-2xl">
                @if($vehicles->first() && $vehicles->first()->coverPhotoUrl())
                    <img src="{{ $vehicles->first()->coverPhotoUrl() }}" alt="Kendaraan andalan {{ $brand['name'] }}" width="800" height="500" class="absolute inset-0 h-full w-full object-cover" fetchpriority="high">
                    <div class="absolute inset-0 bg-gradient-to-t from-fleet-950/85 via-fleet-950/10 to-transparent" aria-hidden="true"></div>
                @else
                    <div class="absolute inset-0 bg-[radial-gradient(60%_60%_at_50%_40%,rgba(56,189,248,.22),transparent)]" aria-hidden="true"></div>
                @endif
                @if($vehicles->first())
                    <div class="absolute inset-x-5 bottom-5 flex items-center justify-between rounded-2xl border border-white/10 bg-fleet-950/80 p-4 backdrop-blur">
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-[.14em] text-sky-300">Mulai dari</p>
                            <p class="mt-1 font-display text-xl font-extrabold">Rp {{ number_format((float) $vehicles->min('daily_rate'), 0, ',', '.') }}<span class="text-sm font-medium text-slate-400">/hari</span></p>
                        </div>
                        <a href="{{ route('pseo.vehicle-detail', $vehicles->first()) }}" class="rounded-xl bg-white px-4 py-2.5 text-sm font-extrabold text-fleet-950 transition hover:bg-slate-200">Lihat unit</a>
                    </div>
                @endif
            </div>
        </div>

        <div class="relative mx-auto -mb-14 max-w-6xl px-5 lg:px-8">
            <x-storefront.search-form :locations="$locations" />
        </div>
    </section>

    <section class="pb-20 pt-28 lg:pt-36" aria-labelledby="armada-heading">
        <div class="mx-auto max-w-7xl px-5 lg:px-8">
            <div class="reveal flex items-end justify-between gap-5">
                <div>
                    <p class="text-xs font-extrabold uppercase tracking-[.2em] text-sky-700">Armada pilihan</p>
                    <h2 id="armada-heading" class="mt-3 font-display text-3xl font-extrabold tracking-tight lg:text-4xl">Mobil tepat untuk setiap agenda</h2>
                </div>
                <a href="{{ route('storefront.catalog') }}" class="hidden shrink-0 text-sm font-bold text-sky-800 transition hover:text-sky-900 sm:block">Jelajahi seluruh armada &rarr;</a>
            </div>
            <div class="mt-10 grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                @forelse($vehicles as $vehicle)
                    <x-storefront.vehicle-card :vehicle="$vehicle" :show-status="true" />
                @empty
                    <div class="col-span-full rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center">
                        <h3 class="font-display text-xl font-bold">Armada sedang diperbarui</h3>
                        <p class="mt-2 text-slate-500">Hubungi tim kami untuk rekomendasi kendaraan yang tersedia hari ini.</p>
                    </div>
                @endforelse
            </div>
            <div class="mt-10 text-center sm:hidden">
                <a href="{{ route('storefront.catalog') }}" class="inline-flex rounded-xl bg-fleet-950 px-6 py-3 text-sm font-extrabold text-white">Jelajahi seluruh armada</a>
            </div>
        </div>
    </section>

    <section class="border-y border-slate-200 bg-white py-20" aria-labelledby="kategori-heading">
        <div class="mx-auto max-w-7xl px-5 lg:px-8">
            <div class="reveal max-w-2xl">
                <p class="text-xs font-extrabold uppercase tracking-[.2em] text-brass-600">Kategori kendaraan</p>
                <h2 id="kategori-heading" class="mt-3 font-display text-3xl font-extrabold tracking-tight lg:text-4xl">Pilih sesuai kebutuhan perjalanan</h2>
            </div>
            <div class="mt-10 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
                @forelse($categories as $category)
                    @php($catVehicle = $category->vehicles->firstWhere('is_active', true))
                    <a href="{{ route('storefront.category', $category->slug) }}" class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white transition duration-300 hover:-translate-y-1 hover:shadow-[0_24px_55px_-35px_rgba(15,23,42,.4)]">
                        <div class="aspect-[4/3] overflow-hidden bg-slate-100">
                            @if($catVehicle && $catVehicle->coverPhotoUrl())
                                <img src="{{ $catVehicle->coverPhotoUrl() }}" alt="Kategori {{ $category->name }}" width="400" height="300" class="h-full w-full object-cover transition duration-500 group-hover:scale-105" loading="lazy" decoding="async" onerror="this.remove()">
                            @endif
                        </div>
                        <div class="p-4">
                            <h3 class="text-sm font-extrabold text-slate-950">{{ $category->name }}</h3>
                            <p class="mt-0.5 text-xs text-slate-500">{{ $category->vehicles_count ?? $category->vehicles->count() }} unit</p>
                        </div>
                    </a>
                @empty
                    <p class="col-span-full text-sm text-slate-500">Kategori kendaraan akan tampil setelah armada ditambahkan.</p>
                @endforelse
            </div>
        </div>
    </section>

    <section class="py-20" aria-labelledby="promo-heading">
        <div class="mx-auto max-w-7xl px-5 lg:px-8">
            <div class="reveal flex items-end justify-between gap-5">
                <div>
                    <p class="text-xs font-extrabold uppercase tracking-[.2em] text-sky-700">Promo aktif</p>
                    <h2 id="promo-heading" class="mt-3 font-display text-3xl font-extrabold tracking-tight lg:text-4xl">Hemat untuk perjalanan berikutnya</h2>
                </div>
            </div>
            <div class="mt-10 grid gap-5 md:grid-cols-3">
                @forelse($promos as $promo)
                    <article class="reveal relative overflow-hidden rounded-2xl border border-slate-200 bg-gradient-to-br from-fleet-900 to-fleet-950 p-7 text-white">
                        <div class="absolute -right-6 -top-10 h-32 w-32 rounded-full bg-sky-400/10" aria-hidden="true"></div>
                        <p class="text-xs font-bold uppercase tracking-[.18em] text-brass-500">{{ $promo->code }}</p>
                        <h3 class="mt-3 font-display text-xl font-extrabold">{{ $promo->name }}</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-300">{{ $promo->description ?? '' }}</p>
                        <div class="mt-6 flex items-center justify-between">
                            <span class="font-display text-2xl font-extrabold text-sky-300">
                                @if($promo->discount_type === 'percentage'){{ number_format((float) $promo->discount_value, 0) }}% @else Rp {{ number_format((float) $promo->discount_value, 0, ',', '.') }} @endif
                                <span class="text-xs font-semibold text-slate-400">potongan</span>
                            </span>
                            <a href="{{ route('storefront.catalog', ['available_only' => 1]) }}" class="rounded-xl bg-white px-4 py-2.5 text-sm font-extrabold text-fleet-950 transition hover:bg-slate-200">Pakai promo</a>
                        </div>
                        @if($promo->end_date)
                            <p class="mt-4 text-xs text-slate-400">Berlaku s.d. {{ $promo->end_date->translatedFormat('d M Y') }}@if($promo->min_rental_days > 1) · min. {{ $promo->min_rental_days }} hari @endif</p>
                        @endif
                    </article>
                @empty
                    <p class="col-span-full text-sm text-slate-500">Belum ada promo aktif saat ini.</p>
                @endforelse
            </div>
        </div>
    </section>

    <section class="border-y border-slate-200 bg-white py-20" aria-labelledby="lokasi-heading">
        <div class="mx-auto max-w-7xl px-5 lg:px-8">
            <div class="reveal flex items-end justify-between gap-5">
                <div>
                    <p class="text-xs font-extrabold uppercase tracking-[.2em] text-brass-600">Lokasi populer</p>
                    <h2 id="lokasi-heading" class="mt-3 font-display text-3xl font-extrabold tracking-tight lg:text-4xl">Ambil mobil di kota Anda</h2>
                </div>
                <a href="{{ route('storefront.locations') }}" class="hidden shrink-0 text-sm font-bold text-sky-800 sm:block">Semua lokasi &rarr;</a>
            </div>
            <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @forelse($locations as $location)
                    @php($cityVehicles = $location->vehicles->where('is_active', true))
                    <a href="{{ $location->city ? route('pseo.category-city', \Illuminate\Support\Str::slug($location->city)) : route('storefront.catalog') }}" class="reveal group rounded-2xl border border-slate-200 bg-[#f8fafb] p-6 transition duration-300 hover:-translate-y-1 hover:border-slate-300 hover:shadow-[0_24px_55px_-35px_rgba(15,23,42,.4)]">
                        <div class="flex items-center justify-between">
                            <h3 class="font-display text-lg font-extrabold text-slate-950">{{ $location->city }}</h3>
                            <span class="text-sky-700 transition group-hover:translate-x-1">&rarr;</span>
                        </div>
                        <p class="mt-2 text-sm text-slate-600">{{ $location->name }} · {{ $location->address }}</p>
                        <p class="mt-3 text-xs font-semibold text-slate-500">{{ $cityVehicles->count() }} kendaraan siap sewa</p>
                    </a>
                @empty
                    <p class="col-span-full text-sm text-slate-500">Informasi cabang akan segera tersedia.</p>
                @endforelse
            </div>
        </div>
    </section>

    <section class="py-20" aria-labelledby="cara-sewa-heading">
        <div class="mx-auto max-w-7xl px-5 lg:px-8">
            <div class="reveal text-center">
                <p class="text-xs font-extrabold uppercase tracking-[.2em] text-sky-700">Cara menyewa</p>
                <h2 id="cara-sewa-heading" class="mt-3 font-display text-3xl font-extrabold tracking-tight lg:text-4xl">Lima langkah, tanpa kejutan</h2>
            </div>
            <ol class="mt-12 grid gap-5 md:grid-cols-5">
                @foreach([['Cari Mobil', 'Tentukan lokasi dan tanggal perjalanan Anda.'], ['Pilih Mobil', 'Bandingkan spesifikasi dan harga transparan.'], ['Lengkapi Data', 'Isi data penyewa dan unggah dokumen.'], ['Bayar', 'Transfer bank, QRIS, atau tunai di kantor.'], ['Ambil Mobil', 'Serah terima digital dengan catat kondisi.']] as $i => [$title, $copy])
                    <li class="reveal relative rounded-2xl border border-slate-200 bg-white p-6">
                        <span class="font-mono text-xs font-bold text-brass-600">0{{ $i + 1 }}</span>
                        <h3 class="mt-6 font-display text-lg font-extrabold">{{ $title }}</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-600">{{ $copy }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    <section class="border-y border-slate-200 bg-white py-20" aria-labelledby="testimoni-heading">
        <div class="mx-auto max-w-7xl px-5 lg:px-8">
            <div class="grid gap-10 lg:grid-cols-[.75fr_1.25fr]">
                <div class="reveal">
                    <p class="text-xs font-extrabold uppercase tracking-[.2em] text-sky-700">Dipercaya pelanggan</p>
                    <h2 id="testimoni-heading" class="mt-3 font-display text-3xl font-extrabold tracking-tight lg:text-4xl">Layanan yang dapat diandalkan setiap detailnya.</h2>
                    <p class="mt-5 max-w-md leading-7 text-slate-600">Ulasan ditampilkan dari data pelanggan terverifikasi yang dikelola tim, bukan angka buatan.</p>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    @forelse($testimonials as $testimonial)
                        <figure class="reveal flex min-h-48 flex-col justify-between rounded-2xl border border-slate-200 bg-[#f8fafb] p-6">
                            <div>
                                <div class="text-sm tracking-[.18em] text-brass-500" aria-label="{{ $testimonial->rating }} dari 5 bintang">{{ str_repeat('★', (int) $testimonial->rating) }}</div>
                                <blockquote class="mt-4 text-base font-semibold leading-7 text-slate-800">&ldquo;{{ $testimonial->content }}&rdquo;</blockquote>
                            </div>
                            <figcaption class="mt-6 border-t border-slate-200 pt-4">
                                <strong class="block text-sm text-slate-950">{{ $testimonial->name }}</strong>
                                <span class="text-xs text-slate-500">{{ $testimonial->company ?? 'Pelanggan terverifikasi' }}</span>
                            </figcaption>
                        </figure>
                    @empty
                        <div class="sm:col-span-2 rounded-2xl border border-dashed border-slate-300 p-10 text-center text-slate-500">Ulasan pelanggan akan tampil setelah dipublikasikan.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </section>

    <section class="py-20 lg:py-28" aria-labelledby="faq-heading">
        <div class="mx-auto grid max-w-7xl gap-10 px-5 lg:grid-cols-[.7fr_1.3fr] lg:px-8">
            <div class="reveal">
                <p class="text-xs font-extrabold uppercase tracking-[.2em] text-brass-600">Informasi sebelum berangkat</p>
                <h2 id="faq-heading" class="mt-3 font-display text-3xl font-extrabold tracking-tight">Pertanyaan yang sering muncul.</h2>
                <a href="{{ route('faq.index') }}" class="mt-6 inline-flex text-sm font-extrabold text-sky-800 transition hover:text-sky-900">Lihat semua FAQ &rarr;</a>
            </div>
            <div class="divide-y divide-slate-200 border-y border-slate-200" x-data="{ open: 0 }">
                @forelse($faqs as $faq)
                    <article class="py-1">
                        <button type="button" @click="open = open === {{ $loop->index }} ? -1 : {{ $loop->index }}" class="flex min-h-16 w-full items-center justify-between gap-5 py-4 text-left font-bold text-slate-950" :aria-expanded="open === {{ $loop->index }}" aria-controls="faq-{{ $faq->id }}">
                            <span>{{ $faq->question }}</span>
                            <span class="text-xl font-light text-sky-700" x-text="open === {{ $loop->index }} ? '−' : '+'" aria-hidden="true"></span>
                        </button>
                        <div id="faq-{{ $faq->id }}" x-show="open === {{ $loop->index }}" x-transition.opacity x-cloak class="pb-5 pr-10 text-sm leading-7 text-slate-600">{{ $faq->answer }}</div>
                    </article>
                @empty
                    <p class="py-8 text-slate-500">Informasi FAQ sedang disiapkan.</p>
                @endforelse
            </div>
        </div>
    </section>

    <section class="pb-4" aria-labelledby="cta-heading">
        <div class="mx-auto max-w-7xl px-5 lg:px-8">
            <div class="relative overflow-hidden rounded-[2rem] bg-brass-500 px-7 py-12 text-fleet-950 sm:px-12 lg:flex lg:items-center lg:justify-between">
                <div class="absolute -right-8 top-1/2 h-px w-1/2 -rotate-6 bg-fleet-950/20" aria-hidden="true"></div>
                <div class="relative">
                    <h2 id="cta-heading" class="font-display text-3xl font-extrabold">Cari Mobil untuk Perjalanan Anda</h2>
                    <p class="mt-2 text-fleet-900/75">Cek ketersediaan dan estimasi harga sekarang — proses hanya beberapa menit.</p>
                </div>
                <a href="{{ route('storefront.catalog', ['available_only' => 1]) }}" class="relative mt-7 inline-flex min-h-12 items-center rounded-xl bg-fleet-950 px-6 font-extrabold text-white transition hover:-translate-y-0.5 lg:mt-0">Cari mobil <span class="ml-3" aria-hidden="true">&rarr;</span></a>
            </div>
        </div>
    </section>
@endsection

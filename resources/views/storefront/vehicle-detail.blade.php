@extends('storefront.layout')

@php($brand = app(\App\Services\WhitelabelService::class)->viewData())
@php($coverUrl = $cover?->url)
@php($bookUrl = route('booking.index', array_filter([
    'vehicle' => $vehicle->id,
    'start_date' => $search['pickup_date'],
    'end_date' => $search['return_date'],
    'pickup_location' => $search['location'],
    'rental_type' => $search['rental_type'],
], fn ($v) => filled($v))))
@php($statusLabel = ['available' => 'Tersedia', 'reserved' => 'Direservasi', 'rented' => 'Sedang Disewa', 'preparing' => 'Disiapkan', 'maintenance' => 'Sedang Servis', 'damaged' => 'Perbaikan'][$vehicle->status] ?? ucfirst($vehicle->status))
@php($availableNow = $vehicle->status === 'available' && $check['available'])

@section('title', 'Sewa '.$vehicle->name.' '.$vehicle->year.' — Rp '.number_format((float) $vehicle->daily_rate, 0, ',', '.').'/Hari | '.$brand['name'])
@section('seoDescription', 'Sewa '.$vehicle->name.' '.$vehicle->year.'. '.$vehicle->category?->name.' · '.ucfirst($vehicle->transmission).' · '.$vehicle->seat_count.' kursi. Harga transparan, serah terima digital, armada terawat di '.$vehicle->location?->name.'.')
@section('ogType', 'product')
@section('ogImage', $coverUrl)

@section('head')
    <script type="application/ld+json">{!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'Product',
        'name' => $vehicle->name.' '.$vehicle->year,
        'description' => $vehicle->description ?? ('Sewa '.$vehicle->name.' harian, mingguan, dan bulanan.'),
        'image' => $coverUrl,
        'brand' => ['@type' => 'Brand', 'name' => $vehicle->brand?->name ?? $brand['name']],
        'offers' => [
            '@type' => 'Offer',
            'price' => (float) $vehicle->daily_rate,
            'priceCurrency' => 'IDR',
            'availability' => $availableNow ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
            'url' => url()->current(),
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    <script type="application/ld+json">{!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Beranda', 'item' => route('home')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Sewa Mobil', 'item' => route('storefront.catalog')],
            ['@type' => 'ListItem', 'position' => 3, 'name' => $vehicle->category?->name ?? 'Armada', 'item' => $vehicle->category ? route('storefront.category', $vehicle->category->slug) : route('storefront.catalog')],
            ['@type' => 'ListItem', 'position' => 4, 'name' => $vehicle->name, 'item' => url()->current()],
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endsection

@section('content')
    <section class="mx-auto max-w-7xl px-5 pb-24 pt-28 lg:px-8 lg:pt-36">
        <nav class="text-xs font-semibold text-slate-400" aria-label="Breadcrumb">
            <ol class="flex flex-wrap items-center gap-2">
                <li><a href="{{ route('home') }}" class="transition hover:text-slate-700">Beranda</a></li>
                <li aria-hidden="true">/</li>
                <li><a href="{{ route('storefront.catalog') }}" class="transition hover:text-slate-700">Sewa Mobil</a></li>
                @if($vehicle->category)
                    <li aria-hidden="true">/</li>
                    <li><a href="{{ route('storefront.category', $vehicle->category->slug) }}" class="transition hover:text-slate-700">{{ $vehicle->category->name }}</a></li>
                @endif
                <li aria-hidden="true">/</li>
                <li aria-current="page" class="text-slate-700">{{ $vehicle->name }}</li>
            </ol>
        </nav>

        <div class="mt-6 grid gap-10 lg:grid-cols-[1.55fr_1fr]">
            <div>
                {{-- Gallery --}}
                <div x-data="gallery()" class="lg:flex lg:gap-4">
                    <div class="relative overflow-hidden rounded-2xl bg-slate-100 lg:flex-1">
                        <div class="aspect-[16/10] w-full">
                            @if($coverUrl)
                                <img id="gallery-main" src="{{ $coverUrl }}" alt="{{ $cover?->alt_text ?? 'Foto '.$vehicle->name }}" width="1200" height="750" class="h-full w-full cursor-zoom-in object-cover transition-opacity duration-300" fetchpriority="high" @click="lightbox = true">
                            @else
                                <div class="grid h-full place-items-center bg-[linear-gradient(145deg,#e8edf2,#f8fafc)]">
                                    <svg class="h-24 w-24 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.25" d="M3 13l2-5a2 2 0 011.9-1.4h10.2A2 2 0 0119 8l2 5m-18 0v5m18-5v5M5 18h14M6 13h.01M18 13h.01M5 13h14"/></svg>
                                </div>
                            @endif
                        </div>
                        @if($gallery->count() > 1)
                            <button type="button" class="absolute bottom-4 right-4 rounded-xl bg-fleet-950/85 px-4 py-2 text-xs font-bold text-white backdrop-blur transition hover:bg-fleet-950" @click="lightbox = true">
                                Lihat Semua Foto ({{ $gallery->count() }})
                            </button>
                        @endif
                    </div>

                    @if($gallery->count() > 1)
                        <div class="mt-3 flex gap-3 overflow-x-auto pb-1 lg:mt-0 lg:w-28 lg:flex-col lg:overflow-visible">
                            @foreach($gallery as $photo)
                                <button type="button" class="aspect-[4/3] w-24 shrink-0 overflow-hidden rounded-xl border-2 transition lg:w-full" :class="active === {{ $loop->index }} ? 'border-sky-600' : 'border-transparent opacity-80 hover:opacity-100'" @click="setActive({{ $loop->index }})" aria-label="Lihat foto {{ $loop->iteration }}">
                                    <img src="{{ $photo->url }}" alt="{{ $photo->alt_text ?? 'Foto '.$vehicle->name.' '.($loop->index + 1) }}" width="200" height="150" class="h-full w-full object-cover" loading="lazy" decoding="async">
                                </button>
                            @endforeach
                        </div>
                    @endif

                    {{-- Lightbox --}}
                    <div x-show="lightbox" x-cloak x-transition.opacity class="fixed inset-0 z-[80] flex items-center justify-center bg-fleet-950/95 p-4" role="dialog" aria-modal="true" aria-label="Galeri foto {{ $vehicle->name }}" @click.self="lightbox = false" @keydown.escape.window="lightbox = false" x-trap.inert.noscroll="lightbox">
                        <button type="button" class="absolute right-5 top-5 grid h-11 w-11 place-items-center rounded-xl border border-white/20 text-white transition hover:bg-white/10" @click="lightbox = false" aria-label="Tutup galeri">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                        <button type="button" class="absolute left-3 grid h-11 w-11 place-items-center rounded-xl border border-white/20 text-white transition hover:bg-white/10" @click="prev()" aria-label="Foto sebelumnya">&larr;</button>
                        <figure class="max-h-[85vh] max-w-4xl" @click.self="lightbox = false">
                            <img :src="photos[active]" :alt="alts[active]" class="max-h-[78vh] w-auto rounded-2xl object-contain">
                            <figcaption class="mt-3 text-center text-sm text-slate-400"><span x-text="active + 1"></span> / {{ $gallery->count() }} — <span x-text="alts[active]"></span></figcaption>
                        </figure>
                        <button type="button" class="absolute right-3 grid h-11 w-11 place-items-center rounded-xl border border-white/20 text-white transition hover:bg-white/10" @click="next()" aria-label="Foto berikutnya">&rarr;</button>
                    </div>
                </div>

                {{-- Header --}}
                <div class="mt-8">
                    <p class="text-xs font-extrabold uppercase tracking-[.16em] text-sky-700">{{ $vehicle->category?->name ?? 'Armada pilihan' }}</p>
                    <h1 class="mt-2 font-display text-3xl font-extrabold tracking-tight lg:text-4xl">{{ $vehicle->name }} {{ $vehicle->year }}</h1>
                    <div class="mt-3 flex flex-wrap items-center gap-x-5 gap-y-2 text-sm font-semibold text-slate-600">
                        <span>{{ $vehicle->brand?->name }}</span>
                        <span aria-hidden="true">·</span>
                        <span>{{ ucfirst($vehicle->transmission) }}</span>
                        <span aria-hidden="true">·</span>
                        <span>{{ $vehicle->seat_count }} kursi</span>
                        <span aria-hidden="true">·</span>
                        <span>{{ ['pertalite' => 'Bensin', 'pertamax' => 'Bensin', 'premium' => 'Bensin', 'diesel' => 'Diesel', 'electric' => 'Listrik'][$vehicle->fuel_type] ?? ucfirst($vehicle->fuel_type) }}</span>
                        <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-bold {{ $availableNow ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-700' }}">
                            <span class="h-2 w-2 rounded-full {{ $availableNow ? 'bg-emerald-500' : 'bg-amber-500' }}" aria-hidden="true"></span>
                            {{ $availableNow ? 'Tersedia untuk tanggal terpilih' : $statusLabel }}
                        </span>
                    </div>
                    <p class="mt-3 flex items-center gap-1.5 text-sm text-slate-500">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        {{ $vehicle->location?->name ?? 'Seluruh cabang' }}@if($vehicle->location?->address) — {{ $vehicle->location->address }}@endif
                    </p>
                </div>

                @if($vehicle->description)
                    <div class="mt-8 rounded-2xl border border-slate-200 bg-white p-6">
                        <h2 class="font-display text-lg font-extrabold">Tentang kendaraan ini</h2>
                        <p class="mt-3 leading-7 text-slate-600">{{ $vehicle->description }}</p>
                    </div>
                @endif

                {{-- Specs --}}
                <div class="mt-6">
                    <h2 class="font-display text-lg font-extrabold">Spesifikasi</h2>
                    <dl class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3">
                        @foreach(array_filter([
                            ['label' => 'Tahun', 'value' => $vehicle->year],
                            ['label' => 'Transmisi', 'value' => ucfirst($vehicle->transmission)],
                            ['label' => 'BBM', 'value' => ['pertalite' => 'Bensin', 'pertamax' => 'Bensin', 'premium' => 'Bensin', 'diesel' => 'Diesel', 'electric' => 'Listrik'][$vehicle->fuel_type] ?? ucfirst($vehicle->fuel_type)],
                            ['label' => 'Kursi', 'value' => $vehicle->seat_count.' kursi'],
                            ['label' => 'Warna', 'value' => $vehicle->color],
                            ['label' => 'Kilometer', 'value' => number_format((int) $vehicle->mileage, 0, ',', '.').' km'],
                            ['label' => 'Kapasitas Mesin', 'value' => $vehicle->engine_cc ? $vehicle->engine_cc.' cc' : null],
                        ], fn ($spec) => filled($spec['value'])) as $spec)
                            <div class="rounded-xl border border-slate-200 bg-white p-4">
                                <dt class="text-xs font-semibold text-slate-500">{{ $spec['label'] }}</dt>
                                <dd class="mt-1 text-sm font-bold text-slate-900">{{ $spec['value'] }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </div>

                {{-- Features (only when data exists) --}}
                @if($vehicle->features && count($vehicle->features) > 0)
                    <div class="mt-8">
                        <h2 class="font-display text-lg font-extrabold">Fasilitas</h2>
                        <ul class="mt-4 flex flex-wrap gap-2">
                            @foreach($vehicle->features as $feature)
                                <li class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700">{{ $feature }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Rental terms --}}
                <div class="mt-8 rounded-2xl border border-slate-200 bg-white p-6">
                    <h2 class="font-display text-lg font-extrabold">Ketentuan sewa</h2>
                    <dl class="mt-4 grid gap-x-8 gap-y-4 sm:grid-cols-2">
                        @foreach(array_filter([
                            ['label' => 'Tarif harian', 'value' => 'Rp '.number_format((float) $vehicle->daily_rate, 0, ',', '.')],
                            ['label' => 'Tarif mingguan', 'value' => (float) $vehicle->weekly_rate > 0 ? 'Rp '.number_format((float) $vehicle->weekly_rate, 0, ',', '.') : null],
                            ['label' => 'Tarif bulanan', 'value' => (float) $vehicle->monthly_rate > 0 ? 'Rp '.number_format((float) $vehicle->monthly_rate, 0, ',', '.') : null],
                            ['label' => 'Deposit', 'value' => (float) $vehicle->deposit_amount > 0 ? 'Rp '.number_format((float) $vehicle->deposit_amount, 0, ',', '.').' (dikembalikan penuh)' : 'Sesuai ketentuan'],
                            ['label' => 'Denda keterlambatan', 'value' => (float) $vehicle->late_fee_per_hour > 0 ? 'Rp '.number_format((float) $vehicle->late_fee_per_hour, 0, ',', '.').'/jam' : null],
                            ['label' => 'Asuransi', 'value' => $vehicle->is_insured ? 'Kendaraan terasuransi' : null],
                        ], fn ($row) => filled($row['value'])) as $row)
                            <div class="flex items-center justify-between border-b border-slate-100 pb-3 text-sm">
                                <dt class="text-slate-500">{{ $row['label'] }}</dt>
                                <dd class="font-bold text-slate-900">{{ $row['value'] }}</dd>
                            </div>
                        @endforeach
                    </dl>
                    <ul class="mt-5 space-y-2 text-sm leading-6 text-slate-600">
                        <li>• Dokumen sewa: KTP + SIM A yang masih berlaku (pemilik dokumen wajib ikut saat serah terima).</li>
                        <li>• BBM: kendaraan diserahkan dengan tangki sesuai kondisi tercatat dan dikembalikan dalam kondisi setara.</li>
                        <li>• Pembatalan gratis hingga 48 jam sebelum jadwal pengambilan.</li>
                        <li>• Pengembalian di lokasi berbeda tersedia dengan biaya relokasi (dihitung saat checkout).</li>
                    </ul>
                </div>
            </div>

            {{-- Booking card --}}
            <aside aria-label="Formulir sewa">
                <div class="sticky top-28 rounded-2xl border border-slate-200 bg-white p-6 shadow-[0_26px_65px_-36px_rgba(15,23,42,.35)]">
                    <div class="flex items-end justify-between">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[.14em] text-slate-500">Mulai dari</p>
                            <p class="mt-1 font-display text-3xl font-extrabold text-slate-950">Rp {{ number_format((float) $vehicle->daily_rate, 0, ',', '.') }}<span class="text-base font-semibold text-slate-500">/hari</span></p>
                        </div>
                        @if((float) $vehicle->monthly_rate > 0)
                            <p class="text-right text-xs leading-5 text-slate-500">Mingguan Rp {{ number_format((float) $vehicle->weekly_rate, 0, ',', '.') }}<br>Bulanan Rp {{ number_format((float) $vehicle->monthly_rate, 0, ',', '.') }}</p>
                        @endif
                    </div>

                    <form method="GET" action="{{ url()->current() }}" class="mt-6 space-y-3" onchange="this.submit()">
                        <div class="grid grid-cols-2 gap-3">
                            <label class="text-xs font-bold text-slate-500">Tanggal ambil
                                <input type="date" name="pickup_date" value="{{ $search['pickup_date'] }}" min="{{ today()->toDateString() }}" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm font-bold text-slate-900">
                            </label>
                            <label class="text-xs font-bold text-slate-500">Jam ambil
                                <input type="time" name="pickup_time" value="{{ $search['pickup_time'] }}" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm font-bold text-slate-900">
                            </label>
                            <label class="text-xs font-bold text-slate-500">Tanggal kembali
                                <input type="date" name="return_date" value="{{ $search['return_date'] }}" min="{{ today()->addDay()->toDateString() }}" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm font-bold text-slate-900">
                            </label>
                            <label class="text-xs font-bold text-slate-500">Jam kembali
                                <input type="time" name="return_time" value="{{ $search['return_time'] }}" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm font-bold text-slate-900">
                            </label>
                        </div>
                        <label class="block text-xs font-bold text-slate-500">Lokasi pengambilan
                            <select name="location" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm font-bold text-slate-900">
                                @foreach($locations as $location)
                                    <option value="{{ $location->slug }}" @selected($search['location'] === $location->slug)>{{ $location->name }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="block text-xs font-bold text-slate-500">Tipe sewa
                            <select name="rental_type" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm font-bold text-slate-900">
                                <option value="self_drive" @selected($search['rental_type'] === 'self_drive')>Lepas Kunci</option>
                                <option value="with_driver" @selected($search['rental_type'] === 'with_driver')>Dengan Sopir</option>
                            </select>
                        </label>
                        <noscript><button type="submit" class="min-h-12 w-full rounded-xl border border-slate-300 font-bold text-slate-800">Perbarui ketersediaan</button></noscript>
                    </form>

                    @if($availableNow)
                        <a href="{{ $bookUrl }}" class="mt-5 grid min-h-14 place-items-center rounded-xl bg-sky-700 font-extrabold text-white transition hover:bg-sky-800">
                            Sewa Sekarang
                        </a>
                    @else
                        <div class="mt-5 rounded-xl bg-amber-50 p-4 text-sm font-semibold text-amber-800">
                            Mobil tidak tersedia untuk tanggal terpilih. Coba ubah tanggal atau lihat kendaraan serupa di bawah.
                        </div>
                        <a href="{{ route('storefront.catalog', array_filter(['pickup_date' => $search['pickup_date'], 'return_date' => $search['return_date'], 'location' => $search['location'], 'available_only' => 1])) }}" class="mt-3 grid min-h-14 place-items-center rounded-xl bg-fleet-950 font-extrabold text-white transition hover:bg-sky-800">
                            Cari kendaraan tersedia
                        </a>
                    @endif

                    <ul class="mt-6 space-y-2.5 border-t border-slate-100 pt-5 text-xs leading-5 text-slate-500">
                        <li class="flex gap-2"><span class="text-emerald-600" aria-hidden="true">✓</span> Harga transparan — rincian tarif, pajak, dan deposit tampil sebelum konfirmasi</li>
                        <li class="flex gap-2"><span class="text-emerald-600" aria-hidden="true">✓</span> Ketersediaan diverifikasi ulang saat booking dikirim</li>
                        <li class="flex gap-2"><span class="text-emerald-600" aria-hidden="true">✓</span> Serah terima digital dengan catat kondisi kendaraan</li>
                    </ul>
                </div>
            </aside>
        </div>

        {{-- Related vehicles --}}
        @if($related->isNotEmpty())
            <div class="mt-16">
                <h2 class="font-display text-2xl font-extrabold tracking-tight">Mobil serupa</h2>
                <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($related as $relatedVehicle)
                        <x-storefront.vehicle-card :vehicle="$relatedVehicle" />
                    @endforeach
                </div>
            </div>
        @endif
    </section>

    {{-- Mobile sticky booking bar --}}
    <div class="fixed inset-x-0 bottom-0 z-40 border-t border-slate-200 bg-white/95 px-5 py-3 backdrop-blur lg:hidden" role="region" aria-label="Booking cepat">
        <div class="flex items-center justify-between gap-4">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-[.1em] text-slate-500">Mulai dari</p>
                <p class="text-lg font-extrabold text-slate-950">Rp {{ number_format((float) $vehicle->daily_rate, 0, ',', '.') }}<span class="text-xs font-semibold text-slate-500">/hari</span></p>
            </div>
            <a href="{{ $availableNow ? $bookUrl : route('storefront.catalog', array_filter(['pickup_date' => $search['pickup_date'], 'return_date' => $search['return_date'], 'available_only' => 1])) }}" class="rounded-xl bg-sky-700 px-6 py-3.5 text-sm font-extrabold text-white transition hover:bg-sky-800">
                {{ $availableNow ? 'Sewa Sekarang' : 'Cari Tersedia' }}
            </a>
        </div>
    </div>
    <div class="h-20 lg:hidden" aria-hidden="true"></div>
@endsection

@push('scripts')
    @if($gallery->count() > 0)
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('gallery', () => ({
                    active: 0,
                    lightbox: false,
                    photos: @json($gallery->map(fn ($p) => $p->url)),
                    alts: @json($gallery->map(fn ($p) => $p->alt_text ?? $p->caption ?? $vehicle->name)),
                    init() {
                        if (document.getElementById('gallery-main')) {
                            document.getElementById('gallery-main').addEventListener('click', () => { this.lightbox = true });
                        }
                    },
                    setActive(index) {
                        this.active = index;
                        const main = document.getElementById('gallery-main');
                        if (main) {
                            main.src = this.photos[index];
                            main.alt = this.alts[index];
                        }
                    },
                    next() { this.active = (this.active + 1) % this.photos.length; this.setActive(this.active); },
                    prev() { this.active = (this.active - 1 + this.photos.length) % this.photos.length; this.setActive(this.active); },
                }));
            });
        </script>
    @endif
@endpush

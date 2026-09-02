@extends('storefront.layout')

@php
    $brand = app(\App\Services\WhitelabelService::class)->viewData();
@endphp

@section('title', ($activeCategory ? 'Sewa '.$activeCategory->name : 'Sewa Mobil').' — Armada & Harga | '.$brand['name'])
@section('seoDescription', $activeCategory
    ? 'Sewa '.$activeCategory->name.' dengan harga transparan dan armada terawat. Filter tanggal, lokasi, dan spesifikasi sesuai kebutuhan perjalanan Anda.'
    : 'Katalog armada lengkap: filter berdasarkan kategori, harga, transmisi, kursi, BBM, dan ketersediaan tanggal. Sewa harian hingga bulanan.')

@section('content')
@php
    $qs = fn (array $overrides = []) => route('storefront.catalog', array_filter(
        array_merge([
            'location' => $filters['location'],
            'pickup_date' => request('pickup_date'),
            'return_date' => request('return_date'),
            'pickup_time' => request('pickup_time'),
            'return_time' => request('return_time'),
            'rental_type' => request('rental_type'),
            'available_only' => request('available_only'),
        ], $overrides),
        fn ($v) => filled($v)
    ));
@endphp
    <section class="bg-fleet-950 pb-10 pt-28 text-white lg:pt-36">
        <div class="mx-auto max-w-7xl px-5 lg:px-8">
            <nav class="text-xs font-semibold text-slate-400" aria-label="Breadcrumb">
                <ol class="flex items-center gap-2">
                    <li><a href="{{ route('home') }}" class="transition hover:text-white">Beranda</a></li>
                    <li aria-hidden="true">/</li>
                    <li aria-current="page" class="text-slate-200">{{ $activeCategory?->name ?? 'Sewa Mobil' }}</li>
                </ol>
            </nav>
            <h1 class="mt-4 font-display text-3xl font-extrabold tracking-tight sm:text-4xl lg:text-5xl">
                {{ $activeCategory ? 'Sewa '.$activeCategory->name : 'Cari mobil untuk perjalanan Anda' }}
            </h1>
            <p class="mt-3 max-w-2xl leading-7 text-slate-300">{{ $activeCategory?->description ?? 'Bandingkan armada, cek ketersediaan tanggal, dan pesan dengan harga transparan. Semua unit terinspeksi sebelum serah terima.' }}</p>
            <div class="mt-8">
                <x-storefront.search-form :locations="$locations" :compact="true" />
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-5 py-10 lg:px-8" aria-label="Hasil pencarian armada">
        <div class="grid gap-8 lg:grid-cols-[17rem_1fr]">
            {{-- Desktop filter sidebar --}}
            <aside class="hidden lg:block" aria-label="Filter armada">
                <div class="sticky top-28 space-y-6 rounded-2xl border border-slate-200 bg-white p-6">
                    <h2 class="text-sm font-extrabold uppercase tracking-[.12em] text-slate-500">Filter</h2>

                    <form method="GET" action="{{ route('storefront.catalog') }}" id="filter-form" class="space-y-6">
                        @foreach(['pickup_date' => request('pickup_date'), 'return_date' => request('return_date'), 'pickup_time' => request('pickup_time'), 'return_time' => request('return_time'), 'rental_type' => request('rental_type')] as $hiddenKey => $hiddenValue)
                            @if(filled($hiddenValue))<input type="hidden" name="{{ $hiddenKey }}" value="{{ $hiddenValue }}">@endif
                        @endforeach

                        <fieldset>
                            <legend class="text-xs font-extrabold uppercase tracking-[.1em] text-slate-500">Kategori</legend>
                            <div class="mt-3 grid gap-2 text-sm">
                                <label class="flex cursor-pointer items-center gap-2 font-medium {{ $filters['category'] ? 'text-slate-600' : 'font-bold text-slate-950' }}">
                                    <input type="radio" name="category" value="" class="h-4 w-4" @checked(! $filters['category'])> Semua
                                </label>
                                @foreach($categories as $category)
                                    <label class="flex cursor-pointer items-center gap-2 {{ $filters['category'] === $category->slug ? 'font-bold text-slate-950' : 'text-slate-600' }}">
                                        <input type="radio" name="category" value="{{ $category->slug }}" class="h-4 w-4" @checked($filters['category'] === $category->slug)> {{ $category->name }}
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>

                        <fieldset>
                            <legend class="text-xs font-extrabold uppercase tracking-[.1em] text-slate-500">Transmisi</legend>
                            <div class="mt-3 grid gap-2 text-sm">
                                <label class="flex items-center gap-2 text-slate-600"><input type="radio" name="transmission" value="" class="h-4 w-4" @checked(! $filters['transmission'])> Semua</label>
                                <label class="flex items-center gap-2 {{ $filters['transmission'] === 'automatic' ? 'font-bold text-slate-950' : 'text-slate-600' }}"><input type="radio" name="transmission" value="automatic" class="h-4 w-4" @checked($filters['transmission'] === 'automatic')> Automatic</label>
                                <label class="flex items-center gap-2 {{ $filters['transmission'] === 'manual' ? 'font-bold text-slate-950' : 'text-slate-600' }}"><input type="radio" name="transmission" value="manual" class="h-4 w-4" @checked($filters['transmission'] === 'manual')> Manual</label>
                            </div>
                        </fieldset>

                        <fieldset>
                            <legend class="text-xs font-extrabold uppercase tracking-[.1em] text-slate-500">Kapasitas kursi</legend>
                            <div class="mt-3 grid gap-2 text-sm">
                                <label class="flex items-center gap-2 text-slate-600"><input type="radio" name="seats" value="" class="h-4 w-4" @checked(! $filters['seats'])> Semua</label>
                                @foreach([4, 5, 6, 7, 10] as $seatOption)
                                    <label class="flex items-center gap-2 {{ $filters['seats'] == $seatOption ? 'font-bold text-slate-950' : 'text-slate-600' }}">
                                        <input type="radio" name="seats" value="{{ $seatOption }}" class="h-4 w-4" @checked($filters['seats'] == $seatOption)> {{ $seatOption }}+ kursi
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>

                        <fieldset>
                            <legend class="text-xs font-extrabold uppercase tracking-[.1em] text-slate-500">BBM</legend>
                            <div class="mt-3 grid gap-2 text-sm">
                                <label class="flex items-center gap-2 text-slate-600"><input type="radio" name="fuel" value="" class="h-4 w-4" @checked(! $filters['fuel'])> Semua</label>
                                @foreach(['pertalite' => 'Bensin', 'diesel' => 'Diesel', 'electric' => 'Listrik'] as $fuelKey => $fuelLabel)
                                    <label class="flex items-center gap-2 {{ $filters['fuel'] === $fuelKey ? 'font-bold text-slate-950' : 'text-slate-600' }}">
                                        <input type="radio" name="fuel" value="{{ $fuelKey }}" class="h-4 w-4" @checked($filters['fuel'] === $fuelKey)> {{ $fuelLabel }}
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>

                        <fieldset>
                            <legend class="text-xs font-extrabold uppercase tracking-[.1em] text-slate-500">Harga per hari</legend>
                            <div class="mt-3 grid grid-cols-2 gap-2">
                                <label class="text-xs text-slate-500">Min
                                    <input type="number" name="min_price" min="0" step="50000" value="{{ $filters['min_price'] }}" placeholder="{{ number_format((int) $priceBounds['min_price']) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm font-bold text-slate-900">
                                </label>
                                <label class="text-xs text-slate-500">Maks
                                    <input type="number" name="max_price" min="0" step="50000" value="{{ $filters['max_price'] }}" placeholder="{{ number_format((int) $priceBounds['max_price']) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm font-bold text-slate-900">
                                </label>
                            </div>
                        </fieldset>

                        <label class="flex items-center gap-3 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-800">
                            <input type="checkbox" name="available_only" value="1" class="h-4 w-4" @checked($filters['available_only'])> Hanya tersedia
                        </label>

                        <div class="grid gap-2">
                            <button type="submit" class="min-h-12 rounded-xl bg-fleet-950 px-5 font-extrabold text-white transition hover:bg-sky-800">Terapkan Filter</button>
                            <a href="{{ route('storefront.catalog') }}" class="min-h-12 rounded-xl border border-slate-300 px-5 py-3 text-center text-sm font-bold text-slate-700 transition hover:bg-slate-50">Reset</a>
                        </div>
                    </form>
                </div>
            </aside>

            <div>
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <p class="text-sm text-slate-600" role="status">
                        Menampilkan <strong class="text-slate-950">{{ $vehicles->firstItem() ?? 0 }}–{{ $vehicles->lastItem() ?? 0 }}</strong>
                        dari <strong class="text-slate-950">{{ $vehicles->total() }}</strong> kendaraan
                    </p>
                    <div class="flex items-center gap-3">
                        <button type="button" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-bold text-slate-800 lg:hidden" @click="$dispatch('open-filter-drawer')" aria-haspopup="dialog">Filter</button>
                        <label class="text-sm">
                            <span class="sr-only">Urutkan hasil</span>
                            <select onchange="window.location.href=this.value" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-bold text-slate-900">
                                @foreach(['recommended' => 'Rekomendasi', 'price_low' => 'Harga terendah', 'price_high' => 'Harga tertinggi', 'newest' => 'Terbaru'] as $sortKey => $sortLabel)
                                    <option value="{{ $qs(['sort' => $sortKey]) }}" @selected(($filters['sort'] ?? 'recommended') === $sortKey)>{{ $sortLabel }}</option>
                                @endforeach
                            </select>
                        </label>
                    </div>
                </div>

                <div class="mt-6 grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                    @forelse($vehicles as $vehicle)
                        <x-storefront.vehicle-card :vehicle="$vehicle" :show-status="true" />
                    @empty
                        <div class="col-span-full rounded-2xl border border-dashed border-slate-300 bg-white p-14 text-center">
                            <h2 class="font-display text-2xl font-extrabold">Mobil pada tanggal tersebut sedang penuh.</h2>
                            <p class="mx-auto mt-3 max-w-md text-slate-500">Coba ubah tanggal sewa, kurangi filter, atau tampilkan semua kendaraan untuk melihat alternatif.</p>
                            <div class="mt-7 flex flex-wrap justify-center gap-3">
                                <a href="{{ $qs(['available_only' => null]) }}" class="rounded-xl bg-fleet-950 px-6 py-3 text-sm font-extrabold text-white transition hover:bg-sky-800">Tampilkan semua mobil</a>
                                <a href="{{ route('storefront.catalog') }}" class="rounded-xl border border-slate-300 px-6 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-50">Reset filter</a>
                            </div>
                        </div>
                    @endforelse
                </div>

                <div class="mt-10">
                    {{ $vehicles->onEachSide(1)->links('storefront.partials.pagination') }}
                </div>
            </div>
        </div>
    </section>

    {{-- Mobile filter drawer --}}
    <div class="fixed inset-0 z-[60] lg:hidden" x-data="{ filterOpen: false }" @open-filter-drawer.window="filterOpen = true">
        <div x-show="filterOpen" x-cloak x-transition.opacity class="absolute inset-0 bg-fleet-950/60 backdrop-blur-sm" @click="filterOpen = false" aria-hidden="true"></div>
        <div x-show="filterOpen" x-cloak x-transition.translate role="dialog" aria-modal="true" aria-label="Filter armada" class="absolute inset-y-0 right-0 w-full max-w-sm overflow-y-auto bg-white p-6 shadow-2xl">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-extrabold">Filter</h2>
                <button type="button" class="grid h-10 w-10 place-items-center rounded-lg border border-slate-300" @click="filterOpen = false" aria-label="Tutup filter">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="mt-6 scale-[1] origin-top lg:hidden">
                <form method="GET" action="{{ route('storefront.catalog') }}" class="space-y-6" onchange="this.submit()">
                    @foreach(['pickup_date' => request('pickup_date'), 'return_date' => request('return_date'), 'pickup_time' => request('pickup_time'), 'return_time' => request('return_time'), 'rental_type' => request('rental_type'), 'available_only' => request('available_only')] as $hiddenKey => $hiddenValue)
                        @if(filled($hiddenValue))<input type="hidden" name="{{ $hiddenKey }}" value="{{ $hiddenValue }}">@endif
                    @endforeach
                    <fieldset>
                        <legend class="text-xs font-extrabold uppercase tracking-[.1em] text-slate-500">Kategori</legend>
                        <div class="mt-3 grid gap-2 text-sm">
                            <label class="flex items-center gap-2 text-slate-600"><input type="radio" name="category" value="" @checked(! $filters['category'])> Semua</label>
                            @foreach($categories as $category)
                                <label class="flex items-center gap-2 {{ $filters['category'] === $category->slug ? 'font-bold' : 'text-slate-600' }}">
                                    <input type="radio" name="category" value="{{ $category->slug }}" @checked($filters['category'] === $category->slug)> {{ $category->name }}
                                </label>
                            @endforeach
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend class="text-xs font-extrabold uppercase tracking-[.1em] text-slate-500">Transmisi</legend>
                        <div class="mt-3 grid gap-2 text-sm">
                            <label class="flex items-center gap-2 text-slate-600"><input type="radio" name="transmission" value="" @checked(! $filters['transmission'])> Semua</label>
                            <label class="flex items-center gap-2 text-slate-600"><input type="radio" name="transmission" value="automatic" @checked($filters['transmission'] === 'automatic')> Automatic</label>
                            <label class="flex items-center gap-2 text-slate-600"><input type="radio" name="transmission" value="manual" @checked($filters['transmission'] === 'manual')> Manual</label>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend class="text-xs font-extrabold uppercase tracking-[.1em] text-slate-500">Kursi</legend>
                        <div class="mt-3 grid gap-2 text-sm">
                            <label class="flex items-center gap-2 text-slate-600"><input type="radio" name="seats" value="" @checked(! $filters['seats'])> Semua</label>
                            @foreach([4, 5, 6, 7, 10] as $seatOption)
                                <label class="flex items-center gap-2 text-slate-600"><input type="radio" name="seats" value="{{ $seatOption }}" @checked($filters['seats'] == $seatOption)> {{ $seatOption }}+ kursi</label>
                            @endforeach
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend class="text-xs font-extrabold uppercase tracking-[.1em] text-slate-500">BBM</legend>
                        <div class="mt-3 grid gap-2 text-sm">
                            <label class="flex items-center gap-2 text-slate-600"><input type="radio" name="fuel" value="" @checked(! $filters['fuel'])> Semua</label>
                            @foreach(['pertalite' => 'Bensin', 'diesel' => 'Diesel', 'electric' => 'Listrik'] as $fuelKey => $fuelLabel)
                                <label class="flex items-center gap-2 text-slate-600"><input type="radio" name="fuel" value="{{ $fuelKey }}" @checked($filters['fuel'] === $fuelKey)> {{ $fuelLabel }}</label>
                            @endforeach
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend class="text-xs font-extrabold uppercase tracking-[.1em] text-slate-500">Harga per hari</legend>
                        <div class="mt-3 grid grid-cols-2 gap-2">
                            <label class="text-xs text-slate-500">Min<input type="number" name="min_price" min="0" step="50000" value="{{ $filters['min_price'] }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm font-bold"></label>
                            <label class="text-xs text-slate-500">Maks<input type="number" name="max_price" min="0" step="50000" value="{{ $filters['max_price'] }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm font-bold"></label>
                        </div>
                    </fieldset>
                    <label class="flex items-center gap-3 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-800">
                        <input type="checkbox" name="available_only" value="1" @checked($filters['available_only'])> Hanya tersedia
                    </label>
                    <div class="grid gap-2 pb-8">
                        <button type="submit" class="min-h-12 rounded-xl bg-fleet-950 px-5 font-extrabold text-white">Terapkan Filter</button>
                        <a href="{{ route('storefront.catalog') }}" class="min-h-12 rounded-xl border border-slate-300 px-5 py-3 text-center text-sm font-bold text-slate-700">Reset</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

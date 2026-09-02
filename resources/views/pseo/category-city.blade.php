@extends('storefront.layout')

@php($brand = app(\App\Services\WhitelabelService::class)->viewData())
@php($cityVehiclesLink = route('storefront.catalog', $location ? ['location' => $location->slug, 'available_only' => 1] : ['available_only' => 1]))

@section('title', 'Sewa Mobil di '.$city.' — Armada & Harga | '.$brand['name'])
@section('seoDescription', $cityDescription)

@section('content')
    <section class="bg-fleet-950 pb-14 pt-28 text-white lg:pt-36">
        <div class="mx-auto max-w-7xl px-5 lg:px-8">
            <nav class="text-xs font-semibold text-slate-400" aria-label="Breadcrumb">
                <ol class="flex items-center gap-2">
                    <li><a href="{{ route('home') }}" class="transition hover:text-white">Beranda</a></li>
                    <li aria-hidden="true">/</li>
                    <li aria-current="page" class="text-slate-200">{{ $city }}</li>
                </ol>
            </nav>
            <h1 class="mt-4 font-display text-3xl font-extrabold tracking-tight sm:text-4xl lg:text-5xl">Sewa Mobil di {{ $city }}</h1>
            <p class="mt-4 max-w-2xl leading-7 text-slate-300">{{ $cityDescription }}</p>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-5 py-14 lg:px-8" aria-label="Armada di {{ $city }}">
        <h2 class="font-display text-2xl font-extrabold tracking-tight">Armada di {{ $city }}</h2>
        <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @forelse($vehicles as $vehicle)
                <x-storefront.vehicle-card :vehicle="$vehicle" :show-status="true" />
            @empty
                <div class="col-span-full rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center">
                    <h3 class="font-display text-xl font-bold">Armada {{ $city }} sedang diperbarui</h3>
                    <p class="mt-2 text-slate-500">Lihat armada lengkap kami di seluruh cabang atau hubungi tim untuk rekomendasi.</p>
                    <a href="{{ route('storefront.catalog') }}" class="mt-6 inline-flex rounded-xl bg-fleet-950 px-6 py-3 text-sm font-extrabold text-white">Lihat semua armada</a>
                </div>
            @endforelse
        </div>
    </section>

    @if(count($priceRows) > 0)
        <section class="border-y border-slate-200 bg-white py-14" aria-label="Harga sewa per kategori">
            <div class="mx-auto max-w-7xl px-5 lg:px-8">
                <h2 class="font-display text-2xl font-extrabold tracking-tight">Harga sewa di {{ $city }}</h2>
                <p class="mt-2 text-sm text-slate-500">Kisaran tarif harian nyata per kategori, dihitung dari armada aktif.</p>
                <div class="mt-8 overflow-x-auto rounded-2xl border border-slate-200">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 bg-slate-50 text-left text-xs font-extrabold uppercase tracking-[.1em] text-slate-500">
                                <th class="px-5 py-4">Kategori</th>
                                <th class="px-5 py-4">Tarif mulai</th>
                                <th class="px-5 py-4">Unit tersedia</th>
                                <th class="px-5 py-4"><span class="sr-only">Aksi</span></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach($priceRows as $row)
                                <tr>
                                    <td class="px-5 py-4 font-bold text-slate-950">{{ $row['name'] }}</td>
                                    <td class="px-5 py-4 text-slate-700">Rp {{ number_format((float) $row['min_rate'], 0, ',', '.') }}/hari</td>
                                    <td class="px-5 py-4 text-slate-700">{{ $row['units'] }} unit</td>
                                    <td class="px-5 py-4 text-right">
                                        <a href="{{ route('storefront.category', str_replace('-', '', \Illuminate\Support\Str::slug($row['name']))) }}" class="font-bold text-sky-700 transition hover:text-sky-900">Lihat unit &rarr;</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    @endif

    <section class="mx-auto max-w-7xl px-5 py-14 lg:px-8">
        <div class="relative overflow-hidden rounded-[2rem] bg-brass-500 px-7 py-12 text-fleet-950 sm:px-12 lg:flex lg:items-center lg:justify-between">
            <div>
                <h2 class="font-display text-3xl font-extrabold">Butuh mobil di {{ $city }}?</h2>
                <p class="mt-2 text-fleet-900/75">Cek ketersediaan dan estimasi harga sekarang — proses hanya beberapa menit.</p>
            </div>
            <a href="{{ $cityVehiclesLink }}" class="mt-7 inline-flex min-h-12 items-center rounded-xl bg-fleet-950 px-6 font-extrabold text-white transition hover:-translate-y-0.5 lg:mt-0">Cari mobil <span class="ml-3" aria-hidden="true">&rarr;</span></a>
        </div>
    </section>
@endsection

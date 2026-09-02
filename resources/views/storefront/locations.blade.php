@extends('storefront.layout')

@php($brand = app(\App\Services\WhitelabelService::class)->viewData())

@section('title', 'Lokasi & Cabang — '.$brand['name'])
@section('seoDescription', 'Cabang rental mobil di Jakarta, Bandung, dan Surabaya. Lihat armada per kota, alamat kantor, dan jam operasional.')
@section('seoCanonical', route('storefront.locations'))

@section('content')
    <section class="bg-fleet-950 pb-14 pt-28 text-white lg:pt-36">
        <div class="mx-auto max-w-7xl px-5 lg:px-8">
            <p class="text-xs font-extrabold uppercase tracking-[.2em] text-sky-300">Lokasi</p>
            <h1 class="mt-3 font-display text-3xl font-extrabold tracking-tight sm:text-4xl lg:text-5xl">Ambil mobil dekat dari Anda</h1>
            <p class="mt-4 max-w-2xl leading-7 text-slate-300">Pilih cabang terdekat, lihat armada yang tersedia di kota tersebut, dan mulai proses sewa secara online.</p>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-5 py-14 lg:px-8" aria-label="Daftar cabang">
        <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
            @forelse($locations as $location)
                @php($cityVehicles = $location->vehicles->where('is_active', true))
                <article class="flex flex-col rounded-2xl border border-slate-200 bg-white p-7 transition hover:-translate-y-1 hover:shadow-[0_24px_55px_-35px_rgba(15,23,42,.4)]">
                    <h2 class="font-display text-xl font-extrabold">{{ $location->city }}</h2>
                    <p class="mt-1 text-sm font-semibold text-sky-700">{{ $location->name }}@if($location->is_headquarters) · Kantor Pusat @endif</p>
                    <p class="mt-4 text-sm leading-6 text-slate-600">{{ $location->address }}</p>
                    <p class="mt-2 text-sm text-slate-500">{{ $location->phone }}</p>
                    <p class="mt-4 text-xs font-bold uppercase tracking-[.1em] text-slate-400">{{ $cityVehicles->count() }} kendaraan aktif</p>
                    <div class="mt-6 flex-1" >
                        <div class="grid grid-cols-3 gap-2">
                            @foreach($cityVehicles->take(3) as $vehicle)
                                <a href="{{ route('pseo.vehicle-detail', $vehicle) }}" class="aspect-[4/3] overflow-hidden rounded-lg bg-slate-100" title="{{ $vehicle->name }}">
                                    @if($vehicle->coverPhotoUrl())
                                        <img src="{{ $vehicle->coverPhotoUrl() }}" alt="{{ $vehicle->name }}" width="200" height="150" class="h-full w-full object-cover" loading="lazy" onerror="this.remove()">
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    </div>
                    <a href="{{ $location->city ? route('pseo.category-city', \Illuminate\Support\Str::slug($location->city)) : route('storefront.catalog', ['location' => $location->slug]) }}" class="mt-6 grid min-h-12 place-items-center rounded-xl bg-fleet-950 font-bold text-white transition hover:bg-sky-800">
                        Lihat armada {{ $location->city }}
                    </a>
                </article>
            @empty
                <p class="col-span-full text-slate-500">Informasi cabang akan segera tersedia.</p>
            @endforelse
        </div>
    </section>
@endsection

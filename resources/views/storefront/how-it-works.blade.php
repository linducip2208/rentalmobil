@extends('storefront.layout')

@php($brand = app(\App\Services\WhitelabelService::class)->viewData())

@section('title', 'Cara Sewa Mobil — Langkah Booking | '.$brand['name'])
@section('seoDescription', 'Cara menyewa mobil di '.$brand['name'].': cari mobil, pilih unit, lengkapi data, bayar, lalu ambil mobil. Proses cepat dan transparan.')
@section('seoCanonical', route('storefront.how-it-works'))

@section('content')
    <section class="bg-fleet-950 pb-14 pt-28 text-white lg:pt-36">
        <div class="mx-auto max-w-7xl px-5 lg:px-8">
            <p class="text-xs font-extrabold uppercase tracking-[.2em] text-sky-300">Cara sewa</p>
            <h1 class="mt-3 font-display text-3xl font-extrabold tracking-tight sm:text-4xl lg:text-5xl">Lima langkah menuju perjalanan</h1>
            <p class="mt-4 max-w-2xl leading-7 text-slate-300">Proses sewa kami didesain sederhana dan transparan — dari pencarian mobil hingga serah terima kunci.</p>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-5 py-14 lg:px-8">
        <ol class="grid gap-5 md:grid-cols-5">
            @foreach([['Cari Mobil', 'Tentukan lokasi pengambilan, tanggal, dan tipe sewa (lepas kunci atau dengan sopir). Sistem langsung menampilkan unit yang tersedia.'], ['Pilih Mobil', 'Bandingkan spesifikasi, foto, fasilitas, dan harga. Semua harga tampil transparan tanpa biaya tersembunyi.'], ['Lengkapi Data', 'Isi data penyewa dan siapkan dokumen: KTP dan SIM A yang masih berlaku. Data Anda tersimpan aman dan hanya dipakai untuk verifikasi.'], ['Bayar', 'Selesaikan pembayaran sesuai rincian: tarif sewa, tambahan, pajak, dan deposit. Deposit dikembalikan penuh setelah kendaraan diperiksa.'], ['Ambil Mobil', 'Datang ke cabang (atau minta antar), lakukan serah terima digital dengan catat kondisi kendaraan, lalu mulai perjalanan.']] as $i => [$title, $copy])
                <li class="rounded-2xl border border-slate-200 bg-white p-6">
                    <span class="grid h-11 w-11 place-items-center rounded-xl bg-fleet-950 font-display text-lg font-extrabold text-white">0{{ $i + 1 }}</span>
                    <h2 class="mt-6 font-display text-lg font-extrabold">{{ $title }}</h2>
                    <p class="mt-3 text-sm leading-6 text-slate-600">{{ $copy }}</p>
                </li>
            @endforeach
        </ol>

        <div class="mt-14 rounded-[2rem] bg-brass-500 px-7 py-12 text-fleet-950 sm:px-12 lg:flex lg:items-center lg:justify-between">
            <div>
                <h2 class="font-display text-3xl font-extrabold">Siap berangkat?</h2>
                <p class="mt-2 text-fleet-900/75">Cari mobil yang tersedia untuk tanggal perjalanan Anda sekarang.</p>
            </div>
            <a href="{{ route('storefront.catalog', ['available_only' => 1]) }}" class="mt-7 inline-flex min-h-12 items-center rounded-xl bg-fleet-950 px-6 font-extrabold text-white transition hover:-translate-y-0.5 lg:mt-0">Cari Mobil <span class="ml-3" aria-hidden="true">&rarr;</span></a>
        </div>
    </section>
@endsection

@props(['vehicle'])
@php
    $photo = $vehicle->photo_url;
    $photoUrl = $photo ? (str_starts_with($photo, 'http') ? $photo : asset('storage/'.ltrim($photo, '/'))) : null;
@endphp
<article class="group overflow-hidden rounded-[1.35rem] border border-slate-200 bg-white shadow-[0_18px_55px_-40px_rgba(15,23,42,.5)] transition duration-300 hover:-translate-y-1 hover:border-slate-300 hover:shadow-[0_26px_65px_-36px_rgba(15,23,42,.42)]">
    <a href="{{ route('pseo.vehicle-detail', $vehicle) }}" class="relative block aspect-[16/10] overflow-hidden bg-slate-100">
        @if($photoUrl)
            <img src="{{ $photoUrl }}" alt="{{ $vehicle->name }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.025]" loading="lazy" onerror="this.hidden=true;this.nextElementSibling.classList.remove('hidden')">
        @endif
        <div class="{{ $photoUrl ? 'hidden' : '' }} absolute inset-0 grid place-items-center bg-[linear-gradient(145deg,#e8edf2,#f8fafc)]">
            <svg class="h-20 w-20 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.25" d="M3 13l2-5a2 2 0 011.9-1.4h10.2A2 2 0 0119 8l2 5m-18 0v5m18-5v5M5 18h14M6 13h.01M18 13h.01M5 13h14"/></svg>
        </div>
        <span class="absolute left-4 top-4 rounded-full bg-white/90 px-3 py-1 text-[11px] font-bold uppercase tracking-[.12em] text-emerald-700 backdrop-blur">Tersedia</span>
    </a>
    <div class="p-5">
        <div class="flex items-start justify-between gap-4">
            <div><p class="text-[11px] font-bold uppercase tracking-[.14em] text-sky-700">{{ $vehicle->category?->name ?? 'Armada pilihan' }}</p><h3 class="mt-1 text-xl font-extrabold tracking-tight text-slate-950">{{ $vehicle->name }}</h3></div>
            <div class="text-right"><strong class="block text-base text-slate-950">Rp {{ number_format((float) $vehicle->daily_rate, 0, ',', '.') }}</strong><span class="text-xs text-slate-500">per hari</span></div>
        </div>
        <div class="mt-5 grid grid-cols-3 divide-x divide-slate-200 border-y border-slate-100 py-3 text-center text-xs text-slate-600">
            <span>{{ ucfirst($vehicle->transmission ?? 'Otomatis') }}</span><span>{{ $vehicle->seat_count ?? 4 }} kursi</span><span>{{ ucfirst($vehicle->fuel_type ?? 'Bensin') }}</span>
        </div>
        <div class="mt-5 flex gap-2">
            <a href="{{ route('pseo.vehicle-detail', $vehicle) }}" class="flex-1 rounded-xl border border-slate-300 px-4 py-2.5 text-center text-sm font-bold text-slate-700 transition hover:bg-slate-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-sky-700">Detail</a>
            <a href="{{ route('booking.index', ['vehicle' => $vehicle->id]) }}" class="flex-1 rounded-xl bg-slate-950 px-4 py-2.5 text-center text-sm font-bold text-white transition hover:bg-sky-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-sky-700">Sewa sekarang</a>
        </div>
    </div>
</article>

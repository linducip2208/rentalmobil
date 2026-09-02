@props(['vehicle', 'showStatus' => false])

@php
    $coverUrl = $vehicle->coverPhotoUrl();
    $statusLabel = match ($vehicle->status ?? 'available') {
        'available' => 'Tersedia',
        'reserved' => 'Direservasi',
        'rented' => 'Sedang Disewa',
        'preparing' => 'Disiapkan',
        'maintenance' => 'Servis',
        default => null,
    };
@endphp
<article class="group flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-[0_18px_55px_-40px_rgba(15,23,42,.5)] transition duration-300 hover:-translate-y-1 hover:border-slate-300 hover:shadow-[0_26px_65px_-36px_rgba(15,23,42,.42)]">
    <a href="{{ route('storefront.show', $vehicle) }}" class="relative block aspect-[16/10] overflow-hidden bg-slate-100">
        @if($coverUrl)
            <img src="{{ $coverUrl }}" alt="{{ $vehicle->photos->first()?->alt_text ?? 'Foto '.$vehicle->name }}" width="800" height="500" class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.03]" loading="lazy" decoding="async" onerror="this.remove()">
        @endif
        <div class="{{ $coverUrl ? 'hidden' : '' }} absolute inset-0 grid place-items-center bg-[linear-gradient(145deg,#e8edf2,#f8fafc)]">
            <svg class="h-20 w-20 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.25" d="M3 13l2-5a2 2 0 011.9-1.4h10.2A2 2 0 0119 8l2 5m-18 0v5m18-5v5M5 18h14M6 13h.01M18 13h.01M5 13h14"/></svg>
        </div>
        @if($showStatus && $statusLabel)
            <span class="absolute left-4 top-4 rounded-full px-3 py-1 text-[11px] font-bold uppercase tracking-[.1em] backdrop-blur {{ ($vehicle->status ?? '') === 'available' ? 'bg-emerald-50/95 text-emerald-700' : 'bg-slate-100/95 text-slate-700' }}">{{ $statusLabel }}</span>
        @elseif(($vehicle->status ?? '') === 'available')
            <span class="absolute left-4 top-4 rounded-full bg-emerald-50/95 px-3 py-1 text-[11px] font-bold uppercase tracking-[.1em] text-emerald-700 backdrop-blur">Tersedia</span>
        @endif
    </a>
    <div class="flex flex-1 flex-col p-5">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-[.14em] text-sky-700">{{ $vehicle->category?->name ?? 'Armada pilihan' }}</p>
                <h3 class="mt-1 text-lg font-extrabold tracking-tight text-slate-950">{{ $vehicle->name }}</h3>
            </div>
            <div class="text-right">
                <strong class="block text-base text-slate-950">Rp {{ number_format((float) $vehicle->daily_rate, 0, ',', '.') }}</strong>
                <span class="text-xs text-slate-500">per hari</span>
            </div>
        </div>
        <div class="mt-4 grid grid-cols-3 divide-x divide-slate-200 border-y border-slate-100 py-3 text-center text-xs text-slate-600">
            <span>{{ ucfirst($vehicle->transmission ?? '-') }}</span>
            <span>{{ $vehicle->seat_count ?? '-' }} kursi</span>
            <span>{{ ['pertalite' => 'Bensin', 'pertamax' => 'Bensin', 'premium' => 'Bensin', 'diesel' => 'Diesel', 'electric' => 'Listrik'][$vehicle->fuel_type] ?? ucfirst($vehicle->fuel_type ?? '-') }}</span>
        </div>
        <p class="mt-3 flex items-center gap-1.5 text-xs text-slate-500">
            <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            {{ $vehicle->location?->name ?? 'Seluruh cabang' }}
        </p>
        <div class="mt-5 flex gap-2">
            <a href="{{ route('storefront.show', $vehicle) }}" class="flex-1 rounded-xl border border-slate-300 px-4 py-2.5 text-center text-sm font-bold text-slate-700 transition hover:bg-slate-50">Lihat Detail</a>
            <a href="{{ route('booking.index', ['vehicle' => $vehicle->id, 'start_date' => request('pickup_date'), 'end_date' => request('return_date')]) }}" class="flex-1 rounded-xl bg-fleet-950 px-4 py-2.5 text-center text-sm font-bold text-white transition hover:bg-sky-800">Sewa Sekarang</a>
        </div>
    </div>
</article>

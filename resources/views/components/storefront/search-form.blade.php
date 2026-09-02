@props(['locations' => collect(), 'action' => route('storefront.search'), 'compact' => false])
<form method="GET" action="{{ $action }}" {{ $attributes->merge(['class' => 'rounded-[1.35rem] bg-white p-4 text-slate-900 shadow-2xl shadow-slate-950/25 '.($compact ? 'grid gap-3 sm:grid-cols-2 lg:grid-cols-[1.1fr_.9fr_.8fr_.9fr_.8fr_auto]' : 'grid gap-3 sm:grid-cols-2 lg:grid-cols-[1.2fr_1fr_.8fr_1fr_.8fr_auto]')]) }} aria-label="Cari mobil untuk disewa">
    <label class="px-3 py-2 text-xs font-bold text-slate-500">
        Lokasi pengambilan
        <select name="location" class="mt-1 block w-full border-0 p-0 text-sm font-bold outline-none focus:ring-0">
            <option value="">Semua cabang</option>
            @foreach($locations as $location)
                <option value="{{ $location->slug }}" @selected(request('location') === $location->slug)>{{ $location->name }} — {{ $location->city }}</option>
            @endforeach
        </select>
    </label>
    <label class="px-3 py-2 text-xs font-bold text-slate-500">
        Tanggal ambil
        <input type="date" name="pickup_date" value="{{ request('pickup_date') }}" min="{{ today()->toDateString() }}" class="mt-1 block w-full border-0 p-0 text-sm font-bold outline-none focus:ring-0">
    </label>
    <label class="px-3 py-2 text-xs font-bold text-slate-500">
        Jam ambil
        <input type="time" name="pickup_time" value="{{ request('pickup_time', '08:00') }}" class="mt-1 block w-full border-0 p-0 text-sm font-bold outline-none focus:ring-0">
    </label>
    <label class="px-3 py-2 text-xs font-bold text-slate-500">
        Tanggal kembali
        <input type="date" name="return_date" value="{{ request('return_date') }}" min="{{ today()->addDay()->toDateString() }}" class="mt-1 block w-full border-0 p-0 text-sm font-bold outline-none focus:ring-0">
    </label>
    <label class="px-3 py-2 text-xs font-bold text-slate-500">
        Tipe sewa
        <select name="rental_type" class="mt-1 block w-full border-0 p-0 text-sm font-bold outline-none focus:ring-0">
            <option value="self_drive" @selected(request('rental_type', 'self_drive') === 'self_drive')>Lepas Kunci</option>
            <option value="with_driver" @selected(request('rental_type') === 'with_driver')>Dengan Sopir</option>
        </select>
    </label>
    <button type="submit" class="mt-1 min-h-14 rounded-xl bg-sky-700 px-6 font-extrabold text-white transition hover:bg-sky-800 lg:mt-0">
        Cari Mobil
    </button>
</form>

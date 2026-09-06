{{-- Filter armada — dipakai desktop & drawer mobile agar tidak duplikat. Butuh $categories, $filters, $priceBounds. --}}
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
    <p class="mt-2 text-sm font-extrabold text-slate-900" aria-live="polite"><span data-price-label> Rp {{ number_format((int) ($filters['min_price'] ?? $priceBounds['min_price']), 0, ',', '.') }} – Rp {{ number_format((int) ($filters['max_price'] ?? $priceBounds['max_price']), 0, ',', '.') }}</span></p>
    <input type="range" data-price-min-range min="{{ (int) $priceBounds['min_price'] }}" max="{{ (int) $priceBounds['max_price'] }}" step="50000" value="{{ $filters['min_price'] ?? $priceBounds['min_price'] }}" class="mt-3 w-full accent-sky-700" aria-label="Harga minimum">
    <input type="range" data-price-max-range min="{{ (int) $priceBounds['min_price'] }}" max="{{ (int) $priceBounds['max_price'] }}" step="50000" value="{{ $filters['max_price'] ?? $priceBounds['max_price'] }}" class="mt-2 w-full accent-sky-700" aria-label="Harga maksimum">
    <div class="mt-3 grid grid-cols-2 gap-2">
        <label class="text-xs text-slate-500">Min
            <input type="number" name="min_price" data-price-min-number min="0" step="50000" value="{{ $filters['min_price'] }}" placeholder="{{ number_format((int) $priceBounds['min_price']) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm font-bold text-slate-900">
        </label>
        <label class="text-xs text-slate-500">Maks
            <input type="number" name="max_price" data-price-max-number min="0" step="50000" value="{{ $filters['max_price'] }}" placeholder="{{ number_format((int) $priceBounds['max_price']) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm font-bold text-slate-900">
        </label>
    </div>
</fieldset>

<label class="flex items-center gap-3 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-800">
    <input type="checkbox" name="available_only" value="1" class="h-4 w-4" @checked($filters['available_only'])> Hanya tersedia
</label>

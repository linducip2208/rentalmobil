<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Booking Mobil — {{ config('app.name') }}</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&family=Instrument+Serif:wght@700&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script>
tailwind.config = {
    theme: { extend: { colors: {
        fleet: { 950: '#08111f', 900: '#0d1b2d', 800: '#12253d' },
        brass: { 500: '#c69245', 600: '#a97b34' },
    }, fontFamily: { sans: ['Instrument Sans', 'ui-sans-serif', 'sans-serif'] } } },
};
</script>
<style>
[x-cloak]{display:none!important}
@media(prefers-reduced-motion:reduce){*{transition-duration:.01ms!important}}
input:focus-visible,select:focus-visible,textarea:focus-visible{outline:2px solid #0369a1;outline-offset:2px}
</style>
</head>
<body class="min-h-screen bg-[#f6f8fa] font-sans text-slate-950 antialiased" x-data="wizard()" x-init="init()">
<header class="sticky top-0 z-30 border-b border-white/10 bg-fleet-950/95 text-white backdrop-blur">
    <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-5 lg:px-8">
        <a href="{{ route('home') }}" class="flex items-center gap-3 font-extrabold"><span class="grid h-9 w-9 place-items-center rounded-xl bg-gradient-to-br from-sky-500 to-blue-700 text-sm font-black text-white">RM</span>{{ config('app.name') }}</a>
        <a href="{{ route('booking.index') }}" class="text-sm font-bold text-slate-300 transition hover:text-white">Booking Saya</a>
    </div>
</header>

<main class="mx-auto max-w-7xl px-5 py-10 lg:px-8">
    <div class="max-w-3xl">
        <p class="text-xs font-extrabold uppercase tracking-[.2em] text-sky-700">Booking online</p>
        <h1 class="mt-3 text-3xl font-extrabold tracking-tight sm:text-4xl">Sembilan langkah, tanpa kejutan.</h1>
        <p class="mt-4 leading-7 text-slate-600">Estimasi harga dihitung ulang oleh server di setiap langkah — Anda selalu melihat rincian yang sama dengan sistem kami.</p>
    </div>

    @if($errors->any())<div class="mt-6 rounded-xl border border-red-200 bg-red-50 p-4 text-red-700" role="alert">{{ $errors->first() }}</div>@endif

    {{-- Progress indicator --}}
    <ol class="mt-8 grid grid-cols-3 gap-2 sm:grid-cols-9" aria-label="Tahap booking">
        @foreach(['Kendaraan', 'Tipe Sewa', 'Pickup', 'Add-ons', 'Data Diri', 'Dokumen', 'Review', 'Pembayaran', 'Konfirmasi'] as $i => $label)
            <li :class="step >= {{ $i + 1 }} ? 'bg-fleet-950 text-white' : 'bg-white text-slate-400'" class="rounded-xl p-2.5 text-center text-[11px] font-bold sm:text-left">
                <span class="block font-mono">0{{ $i + 1 }}</span>{{ $label }}
            </li>
        @endforeach
    </ol>

    <form id="booking-form" method="POST" action="{{ route('booking.store') }}" enctype="multipart/form-data" class="mt-8 grid gap-6 lg:grid-cols-[1fr_23rem]" @submit="confirming = true">@csrf
        <input type="hidden" name="session_id" :value="sessionId">

        <div class="space-y-6">
            {{-- STEP 1 — Kendaraan & jadwal --}}
            <section x-show="step===1" class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-7">
                <h2 class="text-xl font-extrabold">Pilih kendaraan & jadwal</h2>
                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    @forelse($vehicles as $v)
                        <label class="group cursor-pointer rounded-2xl border border-slate-200 p-4 transition has-[:checked]:border-sky-700 has-[:checked]:ring-2 has-[:checked]:ring-sky-100">
                            <div class="flex items-start gap-3">
                                <input type="radio" name="vehicle_id" value="{{ $v->id }}" class="mt-1" @checked((string) $preselectedVehicle === (string) $v->id) required @change="refreshQuote()">
                                @if($v->coverPhotoUrl())
                                    <img src="{{ $v->coverPhotoUrl() }}" alt="Foto {{ $v->name }}" class="h-16 w-24 rounded-lg object-cover" loading="lazy" onerror="this.remove()">
                                @endif
                                <span>
                                    <b class="block">{{ $v->name }}</b>
                                    <small class="text-slate-500">{{ $v->category?->name }} · {{ ucfirst($v->transmission) }} · {{ $v->seat_count }} kursi</small>
                                    <strong class="mt-2 block text-sky-800">Rp {{ number_format($v->daily_rate,0,',','.') }}<small class="font-normal text-slate-500">/hari</small></strong>
                                </span>
                            </div>
                        </label>
                    @empty
                        <div class="col-span-full rounded-xl border border-dashed p-10 text-center text-slate-500">Belum ada kendaraan tersedia pada periode ini.</div>
                    @endforelse
                </div>
                <div class="mt-6 grid gap-4 sm:grid-cols-2">
                    <label class="text-sm font-bold">Tanggal ambil<input name="start_date" type="date" value="{{ $prefill['start_date'] }}" min="{{ today()->toDateString() }}" required class="mt-2 w-full rounded-xl border border-slate-300 p-3 font-normal" @change="refreshQuote()"></label>
                    <label class="text-sm font-bold">Tanggal kembali<input name="end_date" type="date" value="{{ $prefill['end_date'] }}" min="{{ today()->addDay()->toDateString() }}" required class="mt-2 w-full rounded-xl border border-slate-300 p-3 font-normal" @change="refreshQuote()"></label>
                </div>
                <div class="mt-7 flex justify-end"><button type="button" @click="next()" class="rounded-xl bg-fleet-950 px-6 py-3 font-extrabold text-white transition hover:bg-sky-800">Lanjut · Tipe Sewa</button></div>
            </section>

            {{-- STEP 2 — Jenis rental --}}
            <section x-show="step===2" x-cloak class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-7">
                <h2 class="text-xl font-extrabold">Jenis rental</h2>
                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <label class="cursor-pointer rounded-2xl border border-slate-200 p-5 has-[:checked]:border-sky-700 has-[:checked]:ring-2 has-[:checked]:ring-sky-100">
                        <input type="radio" name="rental_type" value="self_drive" class="mr-2" @checked(($prefill['rental_type'] ?? 'self_drive') === 'self_drive') required @change="refreshQuote()">
                        <b>Lepas Kunci</b><p class="mt-2 text-sm text-slate-500">Anda berkendara sendiri. Wajib SIM A berlaku + dokumen identitas.</p>
                    </label>
                    <label class="cursor-pointer rounded-2xl border border-slate-200 p-5 has-[:checked]:border-sky-700 has-[:checked]:ring-2 has-[:checked]:ring-sky-100">
                        <input type="radio" name="rental_type" value="with_driver" class="mr-2" @checked($prefill['rental_type'] === 'with_driver') required @change="refreshQuote()">
                        <b>Dengan Sopir</b><p class="mt-2 text-sm text-slate-500">Sopir berpengalaman mengantar — tarif sopir masuk otomatis.</p>
                    </label>
                    <label class="cursor-pointer rounded-2xl border border-slate-200 p-5 has-[:checked]:border-sky-700 has-[:checked]:ring-2 has-[:checked]:ring-sky-100 sm:col-span-2 lg:col-span-1">
                        <input type="radio" name="rental_type" value="airport_transfer" class="mr-2" @change="refreshQuote()">
                        <b>Antar–Jemput Bandara</b><p class="mt-2 text-sm text-slate-500">Fokus perjalanan bandara dengan tarif khusus.</p>
                    </label>
                    <label class="cursor-pointer rounded-2xl border border-slate-200 p-5 has-[:checked]:border-sky-700 has-[:checked]:ring-2 has-[:checked]:ring-sky-100 sm:col-span-2 lg:col-span-1">
                        <input type="radio" name="rental_type" value="corporate" class="mr-2" @change="refreshQuote()">
                        <b>Corporate</b><p class="mt-2 text-sm text-slate-500">Untuk kebutuhan perusahaan dengan penagihan korporat.</p>
                    </label>
                </div>
                <div class="mt-7 flex gap-3"><button type="button" @click="step=1" class="rounded-xl border border-slate-300 px-6 py-3 font-bold">Kembali</button><button type="button" @click="next()" class="ml-auto rounded-xl bg-fleet-950 px-6 py-3 font-extrabold text-white transition hover:bg-sky-800">Lanjut · Pickup</button></div>
            </section>

            {{-- STEP 3 — Pickup / return --}}
            <section x-show="step===3" x-cloak class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-7">
                <h2 class="text-xl font-extrabold">Titik ambil & kembali</h2>
                <p class="mt-2 text-sm text-slate-500">Ambil di kantor gratis. Antar ke alamat tersedia di kota cabang — biaya relokasi dihitung otomatis bila kota berbeda.</p>
                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <label class="text-sm font-bold">Lokasi pengambilan
                        <select name="pickup_location_id" required class="mt-2 w-full rounded-xl border border-slate-300 p-3 font-normal" @change="refreshQuote()">
                            <option value="">Pilih lokasi</option>
                            @foreach($locations as $l)<option value="{{ $l->id }}" @selected((string) $prefill['pickup_location_id'] === (string) $l->id)>{{ $l->name }} — {{ $l->city }}</option>@endforeach
                        </select>
                    </label>
                    <label class="text-sm font-bold">Lokasi pengembalian
                        <select name="return_location_id" class="mt-2 w-full rounded-xl border border-slate-300 p-3 font-normal" @change="refreshQuote()">
                            <option value="">Sama dengan pengambilan</option>
                            @foreach($locations as $l)<option value="{{ $l->id }}">{{ $l->name }} — {{ $l->city }}</option>@endforeach
                        </select>
                    </label>
                    <label class="text-sm font-bold sm:col-span-2">Alamat antar / jemput (opsional)
                        <input name="pickup_city" placeholder="Contoh: Hotel Grand Sahid, Jakarta" class="mt-2 w-full rounded-xl border border-slate-300 p-3 font-normal">
                    </label>
                </div>
                <p x-show="quote.relocation_fee > 0" class="mt-4 rounded-xl bg-amber-50 p-3 text-sm font-semibold text-amber-800">Biaya relokasi antar-kota: Rp {{ ' — dihitung server setelah lokasi dipilih.' }} <span x-text="quote.relocation_fee ? 'Rp ' + idr(quote.relocation_fee) : ''"></span></p>
                <div class="mt-7 flex gap-3"><button type="button" @click="step=2" class="rounded-xl border border-slate-300 px-6 py-3 font-bold">Kembali</button><button type="button" @click="next()" class="ml-auto rounded-xl bg-fleet-950 px-6 py-3 font-extrabold text-white transition hover:bg-sky-800">Lanjut · Add-ons</button></div>
            </section>

            {{-- STEP 4 — Add-ons --}}
            <section x-show="step===4" x-cloak class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-7">
                <h2 class="text-xl font-extrabold">Tambahan perjalanan</h2>
                @if($addons->isNotEmpty())
                    <div class="mt-5 grid gap-3 sm:grid-cols-2">
                        @foreach($addons as $addon)
                            <label class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 p-4 text-sm has-[:checked]:border-sky-700 has-[:checked]:ring-2 has-[:checked]:ring-sky-100">
                                <span><input type="checkbox" name="addon_ids[]" value="{{ $addon->id }}" class="mr-2" @change="refreshQuote()">{{ $addon->name }}<small class="block text-slate-400">{{ $addon->price_type === 'daily' || $addon->price_type === 'per_day' ? 'per hari' : 'sekali bayar' }}</small></span>
                                <b>Rp {{ number_format($addon->price,0,',','.') }}</b>
                            </label>
                        @endforeach
                    </div>
                @else
                    <p class="mt-5 text-slate-500">Belum ada add-on yang tersedia.</p>
                @endif
                <div class="mt-7 flex gap-3"><button type="button" @click="step=3" class="rounded-xl border border-slate-300 px-6 py-3 font-bold">Kembali</button><button type="button" @click="next()" class="ml-auto rounded-xl bg-fleet-950 px-6 py-3 font-extrabold text-white transition hover:bg-sky-800">Lanjut · Data Diri</button></div>
            </section>

            {{-- STEP 5 — Data penyewa --}}
            <section x-show="step===5" x-cloak class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-7">
                <h2 class="text-xl font-extrabold">Data penyewa</h2>
                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <label class="text-sm font-bold">Nama lengkap<input name="name" required autocomplete="name" class="mt-2 w-full rounded-xl border border-slate-300 p-3 font-normal"></label>
                    <label class="text-sm font-bold">Nomor WhatsApp<input name="phone" required autocomplete="tel" class="mt-2 w-full rounded-xl border border-slate-300 p-3 font-normal"></label>
                    <label class="text-sm font-bold sm:col-span-2">Email<input name="email" type="email" required autocomplete="email" class="mt-2 w-full rounded-xl border border-slate-300 p-3 font-normal"></label>
                    <label class="text-sm font-bold sm:col-span-2">Kode promo (opsional)<input name="promo_code" placeholder="Misal: HEMAT10" class="mt-2 w-full rounded-xl border border-slate-300 p-3 font-normal uppercase" @change="refreshQuote()"></label>
                    <label class="text-sm font-bold sm:col-span-2">Catatan perjalanan (opsional)<textarea name="notes" rows="2" class="mt-2 w-full rounded-xl border border-slate-300 p-3 font-normal"></textarea></label>
                </div>
                <div class="mt-7 flex gap-3"><button type="button" @click="step=4" class="rounded-xl border border-slate-300 px-6 py-3 font-bold">Kembali</button><button type="button" @click="next()" class="ml-auto rounded-xl bg-fleet-950 px-6 py-3 font-extrabold text-white transition hover:bg-sky-800">Lanjut · Dokumen</button></div>
            </section>

            {{-- STEP 6 — Dokumen --}}
            <section x-show="step===6" x-cloak class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-7">
                <h2 class="text-xl font-extrabold">Dokumen sewa</h2>
                <p class="mt-2 text-sm text-slate-500">Hanya berlaku untuk lepas kunci: KTP, SIM A, dan selfie wajib. Dengan sopir: KTP + SIM cukup. Dokumen tersimpan privat dan hanya diperiksa tim verifikasi.</p>
                <div class="mt-5 grid gap-4 sm:grid-cols-3">
                    <label class="rounded-xl border border-dashed border-slate-300 p-4 text-center text-sm">
                        <b class="block">KTP</b><small class="text-slate-400">JPG/PNG maks 4MB</small>
                        <input type="file" name="document_ktp" accept="image/jpeg,image/png,image/webp" class="mx-auto mt-3 block w-full text-xs" @change="preview($event, 'ktp')">
                        <img x-show="previews.ktp" :src="previews.ktp" class="mx-auto mt-3 h-24 rounded-lg object-cover" alt="Pratinjau KTP">
                    </label>
                    <label class="rounded-xl border border-dashed border-slate-300 p-4 text-center text-sm">
                        <b class="block">SIM A</b><small class="text-slate-400" x-text="isSelfDrive() ? 'Wajib (lepas kunci)' : 'Opsional'">Wajib (lepas kunci)</small>
                        <input type="file" name="document_sim" accept="image/jpeg,image/png,image/webp" class="mx-auto mt-3 block w-full text-xs" @change="preview($event, 'sim')">
                        <img x-show="previews.sim" :src="previews.sim" class="mx-auto mt-3 h-24 rounded-lg object-cover" alt="Pratinjau SIM">
                    </label>
                    <label class="rounded-xl border border-dashed border-slate-300 p-4 text-center text-sm">
                        <b class="block">Selfie dengan KTP</b><small class="text-slate-400" x-text="isSelfDrive() ? 'Wajib (lepas kunci)' : 'Tidak wajib'">Wajib (lepas kunci)</small>
                        <input type="file" name="document_selfie" accept="image/jpeg,image/png,image/webp" class="mx-auto mt-3 block w-full text-xs" @change="preview($event, 'selfie')">
                        <img x-show="previews.selfie" :src="previews.selfie" class="mx-auto mt-3 h-24 rounded-lg object-cover" alt="Pratinjau selfie">
                    </label>
                </div>
                <div class="mt-7 flex gap-3"><button type="button" @click="step=5" class="rounded-xl border border-slate-300 px-6 py-3 font-bold">Kembali</button><button type="button" @click="review()" class="ml-auto rounded-xl bg-fleet-950 px-6 py-3 font-extrabold text-white transition hover:bg-sky-800">Tinjau Harga</button></div>
            </section>

            {{-- STEP 7 — Review harga (server-side quote + hold) --}}
            <section x-show="step===7" x-cloak class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-7">
                <h2 class="text-xl font-extrabold">Review harga</h2>
                <div id="review-breakdown" class="mt-5 space-y-2 text-sm text-slate-600">
                    <p x-show="!quote.total" class="text-slate-400">Pilih kendaraan dan tanggal untuk melihat rincian.</p>
                    <template x-if="quote.total">
                        <div class="space-y-2">
                            <div class="flex justify-between"><span>Tarif efektif <span x-text="quote.duration_days"></span> hari</span><b>Rp <span x-text="idr(quote.base_total)"></span></b></div>
                            <div class="flex justify-between" x-show="quote.addon_total > 0"><span>Add-ons</span><b>Rp <span x-text="idr(quote.addon_total)"></span></b></div>
                            <div class="flex justify-between" x-show="quote.relocation_fee > 0"><span>Relokasi / antar</span><b>Rp <span x-text="idr(quote.relocation_fee)"></span></b></div>
                            <div class="flex justify-between text-emerald-700" x-show="quote.discount_amount > 0"><span>Diskon</span><b>- Rp <span x-text="idr(quote.discount_amount)"></span></b></div>
                            <div class="flex justify-between" x-show="quote.tax_amount > 0"><span>PPN <span x-text="Math.round((quote.tax_rate || 0) * 100)"></span>%</span><b>Rp <span x-text="idr(quote.tax_amount)"></span></b></div>
                            <div class="flex justify-between border-t pt-3 text-base text-slate-950"><strong>Total sewa</strong><strong>Rp <span x-text="idr(quote.total)"></span></strong></div>
                            <div class="flex justify-between text-slate-500"><span>Deposit (dikembalikan penuh)</span><span>Rp <span x-text="idr(quote.deposit)"></span></span></div>
                            <p class="rounded-xl bg-sky-50 p-3 text-xs text-sky-900" x-show="holdMinutesLeft">Slot <b>{{ '' }}</b> unit ini di-hold <span x-text="holdMinutesLeft"></span> menit untuk Anda.</p>
                        </div>
                    </template>
                </div>
                <div class="mt-6 rounded-xl bg-slate-50 p-4 text-sm text-slate-600">Dengan melanjutkan, Anda menyetujui verifikasi ketersediaan, identitas, serta syarat rental yang berlaku. Harga final dihitung ulang server saat konfirmasi.</div>
                <div class="mt-7 flex gap-3"><button type="button" @click="step=6" class="rounded-xl border border-slate-300 px-6 py-3 font-bold">Kembali</button><button type="button" @click="next()" class="ml-auto rounded-xl bg-fleet-950 px-6 py-3 font-extrabold text-white transition hover:bg-sky-800">Lanjut · Pembayaran</button></div>
            </section>

            {{-- STEP 8 — Pembayaran (informasi) --}}
            <section x-show="step===8" x-cloak class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-7">
                <h2 class="text-xl font-extrabold">Pembayaran</h2>
                <p class="mt-2 text-sm text-slate-500">Setelah booking diverifikasi, tagihan terbit di portal pelanggan dengan opsi:</p>
                <ul class="mt-4 grid gap-3 text-sm">
                    <li class="flex items-center gap-3 rounded-xl border border-slate-200 p-4"><b class="font-mono">01</b> Transfer bank (BCA / Mandiri) — verifikasi otomatis</li>
                    <li class="flex items-center gap-3 rounded-xl border border-slate-200 p-4"><b class="font-mono">02</b> QRIS — scan dari e-wallet favorit Anda</li>
                    <li class="flex items-center gap-3 rounded-xl border border-slate-200 p-4"><b class="font-mono">03</b> Tunai di kantor cabang saat pengambilan</li>
                </ul>
                <p class="mt-4 text-xs text-slate-500">Deposit tidak termasuk total sewa dan dikembalikan penuh setelah kendaraan diperiksa.</p>
                <div class="mt-7 flex gap-3"><button type="button" @click="step=7" class="rounded-xl border border-slate-300 px-6 py-3 font-bold">Kembali</button><button type="button" @click="next()" class="ml-auto rounded-xl bg-fleet-950 px-6 py-3 font-extrabold text-white transition hover:bg-sky-800">Lanjut · Konfirmasi</button></div>
            </section>

            {{-- STEP 9 — Konfirmasi --}}
            <section x-show="step===9" x-cloak class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-7">
                <h2 class="text-xl font-extrabold">Konfirmasi booking</h2>
                <p class="mt-2 text-slate-600">Semua siap. Klik tombol di bawah — kami kunci slot dan tim kami menghubungi Anda untuk verifikasi.</p>
                <div class="mt-6 rounded-xl bg-slate-50 p-4 text-sm text-slate-600">Pastikan dokumen sudah diunggah dengan benar untuk mempercepat verifikasi.</div>
                <div class="mt-7 flex gap-3"><button type="button" @click="step=8" class="rounded-xl border border-slate-300 px-6 py-3 font-bold">Kembali</button><button type="submit" :disabled="confirming" class="ml-auto rounded-xl bg-sky-700 px-8 py-3.5 font-extrabold text-white transition hover:bg-sky-800 disabled:opacity-60"><span x-show="!confirming">Kirim Booking</span><span x-cloak x-show="confirming">Mengirim…</span></button></div>
            </section>
        </div>

        {{-- Sticky summary --}}
        <aside class="h-fit rounded-2xl border border-slate-200 bg-white p-5 lg:sticky lg:top-24">
            <p class="text-xs font-extrabold uppercase tracking-[.16em] text-sky-700">Ringkasan harga</p>
            <div class="mt-5 text-sm text-slate-500">
                <p x-show="!quote.total">Pilih kendaraan dan tanggal untuk melihat estimasi biaya.</p>
                <div x-show="quote.total" class="space-y-3">
                    <div class="flex justify-between"><span>Durasi</span><b x-text="quote.duration_days + ' hari'"></b></div>
                    <div class="flex justify-between"><span>Tarif efektif</span><b>Rp <span x-text="idr(quote.effective_daily_rate)"></span>/hari</b></div>
                    <div class="flex justify-between" x-show="quote.addon_total > 0"><span>Tambahan</span><b>Rp <span x-text="idr(quote.addon_total)"></span></b></div>
                    <div class="flex justify-between text-emerald-700" x-show="quote.discount_amount > 0"><span>Diskon</span><b>- Rp <span x-text="idr(quote.discount_amount)"></span></b></div>
                    <div class="flex justify-between border-t pt-4 text-lg text-slate-950"><strong>Total</strong><strong>Rp <span x-text="idr(quote.total)"></span></strong></div>
                </div>
            </div>
            <div class="mt-6 border-t border-slate-100 pt-5 text-xs leading-5 text-slate-500">Harga transparan · Data terenkripsi · Ketersediaan dikunci server</div>
        </aside>
    </form>
</main>

<script>
function wizard() {
    return {
        step: 1,
        confirming: false,
        sessionId: (crypto.randomUUID ? crypto.randomUUID() : String(Date.now()) + Math.random().toString(36).slice(2)),
        quote: {},
        holdMinutesLeft: null,
        previews: { ktp: null, sim: null, selfie: null },
        idr(v) { return Number(v || 0).toLocaleString('id-ID'); },
        init() {
            this.$watch('step', (v) => { if (v === 7) this.refreshQuote(true); });
        },
        isSelfDrive() {
            const f = document.getElementById('booking-form');
            return f?.rental_type?.value === 'self_drive';
        },
        next() {
            const f = document.getElementById('booking-form');
            if (this.step === 1 && (!f.vehicle_id.value || !f.start_date.value || !f.end_date.value)) { alert('Pilih kendaraan dan lengkapi tanggal dulu.'); return; }
            if (this.step === 3 && !f.pickup_location_id.value) { alert('Pilih lokasi pengambilan dulu.'); return; }
            this.step = Math.min(9, this.step + 1);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },
        async review() {
            await this.refreshQuote(true);
            this.next();
        },
        async refreshQuote(withHold = false) {
            const f = document.getElementById('booking-form');
            const v = f.querySelector('[name=vehicle_id]:checked');
            if (!v || !f.start_date.value || !f.end_date.value) return;
            const ids = [...f.querySelectorAll('[name="addon_ids[]"]:checked')].map((x) => x.value);
            try {
                const r = await fetch(@json(route('booking.quote')), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': f._token.value },
                    body: JSON.stringify({
                        vehicle_id: v.value, start_date: f.start_date.value, end_date: f.end_date.value,
                        rental_type: f.rental_type.value, addon_ids: ids, promo_code: f.promo_code.value,
                        pickup_location_id: f.pickup_location_id.value, return_location_id: f.return_location_id.value,
                    }),
                });
                if (!r.ok) throw 0;
                this.quote = await r.json();
            } catch (e) {
                this.quote = {};
            }
            if (withHold) await this.createHold(v.value);
        },
        async createHold(vehicleId) {
            const f = document.getElementById('booking-form');
            try {
                const r = await fetch(@json(route('booking.hold')), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': f._token.value },
                    body: JSON.stringify({ vehicle_id: vehicleId, start_date: f.start_date.value, end_date: f.end_date.value, session_id: this.sessionId }),
                });
                if (r.status === 409) {
                    const d = await r.json();
                    alert(d.message || 'Unit sedang di-hold pemesan lain.');
                    this.step = 1;
                    return;
                }
                if (r.ok) { const d = await r.json(); this.holdMinutesLeft = d.minutes_left; }
            } catch (e) { /* hold bersifat best-effort; server recheck saat submit */ }
        },
        preview(event, key) {
            const file = event.target.files[0];
            this.previews[key] = file ? URL.createObjectURL(file) : null;
        },
    };
}
</script>
</body>
</html>

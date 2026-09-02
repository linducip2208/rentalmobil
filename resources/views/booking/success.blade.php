<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Booking diterima — {{ config('app.name') }}</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script>tailwind.config = { theme: { extend: { colors: { fleet: { 950: '#08111f', 900: '#0d1b2d' } }, fontFamily: { sans: ['Instrument Sans', 'sans-serif'] } } } };</script>
</head>
<body class="grid min-h-screen place-items-center bg-fleet-950 p-4 font-sans text-slate-950">
<div class="w-full max-w-lg rounded-3xl bg-white p-8 text-center shadow-2xl">
    <div class="mx-auto grid h-16 w-16 place-items-center rounded-2xl bg-emerald-50 text-3xl font-black text-emerald-600" aria-hidden="true">✓</div>
    <h1 class="mt-5 text-3xl font-extrabold">Booking diterima</h1>
    <p class="mt-3 text-slate-600">Nomor booking Anda:</p>
    <div class="mt-3 rounded-xl bg-slate-100 p-4 font-mono text-xl font-extrabold">{{ $booking->booking_number }}</div>
    <dl class="mt-6 space-y-2 rounded-xl bg-slate-50 p-4 text-left text-sm">
        <div class="flex justify-between"><dt class="text-slate-500">Kendaraan</dt><dd class="font-bold">{{ $booking->vehicle?->name }}</dd></div>
        <div class="flex justify-between"><dt class="text-slate-500">Periode</dt><dd class="font-bold">{{ $booking->start_date?->format('d M Y') }} — {{ $booking->end_date?->format('d M Y') }}</dd></div>
        <div class="flex justify-between"><dt class="text-slate-500">Total sewa</dt><dd class="font-bold">Rp {{ number_format((float) $booking->total_amount, 0, ',', '.') }}</dd></div>
        @if((float) $booking->deposit_amount > 0)
            <div class="flex justify-between"><dt class="text-slate-500">Deposit (dikembalikan)</dt><dd class="font-bold">Rp {{ number_format((float) $booking->deposit_amount, 0, ',', '.') }}</dd></div>
        @endif
    </dl>
    <p class="mt-5 text-sm leading-6 text-slate-500">Tim kami akan memverifikasi data dan dokumen Anda, lalu mengirim konfirmasi. Simpan nomor booking di atas.</p>
    <a href="{{ route('home') }}" class="mt-6 inline-flex rounded-xl bg-fleet-950 px-6 py-3 font-extrabold text-white transition hover:bg-sky-800">Kembali ke beranda</a>
</div>
</body>
</html>

<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Self Check-in — RentalMobil</title>
    <meta name="robots" content="noindex">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 font-sans text-slate-800 antialiased">
<div class="mx-auto max-w-2xl px-4 py-8">
    @if (session('status'))
        <div class="mb-4 rounded-xl bg-emerald-50 p-4 text-sm font-semibold text-emerald-800">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="mb-4 rounded-xl bg-red-50 p-4 text-sm font-semibold text-red-700">{{ $errors->first() }}</div>
    @endif

    <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
        <div class="border-b border-slate-100 bg-gradient-to-r from-blue-900 to-blue-700 p-6 text-white">
            <div class="font-mono text-xs uppercase tracking-[.2em] text-blue-200">Self Check-in Kendaraan</div>
            <h1 class="mt-1 text-2xl font-extrabold">{{ $order->vehicle?->name }}</h1>
            <p class="mt-1 text-sm text-blue-100">{{ $order->vehicle?->plate_number }} · {{ $order->start_date?->format('d M Y') }} → {{ $order->end_date?->format('d M Y') }}</p>
        </div>

        <form method="post" action="{{ route('handover.checkin.submit', $token) }}" enctype="multipart/form-data" class="space-y-5 p-6">
            @csrf
            <div class="rounded-xl bg-blue-50 p-4 text-sm text-blue-900">
                Foto kondisi mobil <b>sebelum dipakai</b> melindungi deposit Anda. Ambil foto: odometer, indikator BBM, sisi depan/belakang/kiri/kanan.
            </div>

            <div>
                <label class="block text-sm font-bold">Foto kondisi kendaraan <span class="text-red-500">*</span></label>
                <input type="file" name="photos[]" accept="image/*" capture="environment" multiple required class="mt-2 w-full rounded-xl border border-slate-300 p-3 text-sm">
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-bold">Level BBM saat ini</label>
                    <select name="fuel_level" required class="mt-2 w-full rounded-xl border border-slate-300 p-3 text-sm">
                        <option value="full">Penuh</option>
                        <option value="three_quarter">3/4</option>
                        <option value="half">Setengah</option>
                        <option value="quarter">1/4</option>
                        <option value="empty">Kosong</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-bold">Odometer (KM)</label>
                    <input type="number" name="odometer_km" min="0" max="2000000" inputmode="numeric" placeholder="mis. 45230" class="mt-2 w-full rounded-xl border border-slate-300 p-3 text-sm">
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold">Catatan (opsional)</label>
                <textarea name="notes" rows="3" maxlength="1000" placeholder="Lecet kecil di bumper belakang sudah ada sebelum sewa, dsb." class="mt-2 w-full rounded-xl border border-slate-300 p-3 text-sm"></textarea>
            </div>

            <button class="w-full rounded-xl bg-blue-700 px-6 py-4 font-extrabold text-white shadow-lg shadow-blue-700/25 transition hover:bg-blue-800">Kirim Check-in</button>
        </form>
    </div>
    <p class="mt-4 text-center text-xs text-slate-400">© {{ date('Y') }} RentalMobil — serah terima mandiri.</p>
</div>
</body>
</html>

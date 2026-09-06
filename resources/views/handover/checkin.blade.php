<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Self Check-in — RentalMobil</title>
    <meta name="robots" content="noindex">
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={theme:{extend:{colors:{fleet:{950:'#08111f',900:'#0d1b2d'}},fontFamily:{sans:['Instrument Sans','ui-sans-serif','sans-serif']}}}}</script>
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
                <label class="block text-sm font-bold">Foto kondisi kendaraan <span class="text-red-500">*</span> <span class="font-normal text-slate-500">(min. 4, maks. 12 — otomatis dikompresi di HP)</span></label>
                <input id="handover-photos" type="file" name="photos[]" accept="image/*" capture="environment" multiple required class="mt-2 w-full rounded-xl border border-slate-300 p-3 text-sm">
                <p id="photo-status" class="mt-2 text-xs font-semibold text-slate-500" aria-live="polite">Belum ada foto dipilih.</p>
                <div id="photo-preview" class="mt-3 grid grid-cols-3 gap-2"></div>
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

            <button id="checkin-submit" class="w-full rounded-xl bg-blue-700 px-6 py-4 font-extrabold text-white shadow-lg shadow-blue-700/25 transition hover:bg-blue-800 disabled:opacity-60">Kirim Check-in</button>
        </form>
    </div>
    <p class="mt-4 text-center text-xs text-slate-400">© {{ date('Y') }} RentalMobil — serah terima mandiri.</p>
</div>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('handover-photos');
    const preview = document.getElementById('photo-preview');
    const status = document.getElementById('photo-status');
    const submit = document.getElementById('checkin-submit');
    if (!input) return;
    const fmt = (b) => b > 1048576 ? (b / 1048576).toFixed(1) + ' MB' : Math.max(1, Math.round(b / 1024)) + ' KB';
    const compress = (file) => new Promise((resolve) => {
        const img = new Image();
        const url = URL.createObjectURL(file);
        img.onload = () => {
            const maxSide = 1600;
            let { width: w, height: h } = img;
            const scale = Math.min(1, maxSide / Math.max(w, h));
            w = Math.round(w * scale); h = Math.round(h * scale);
            const c = document.createElement('canvas');
            c.width = w; c.height = h;
            c.getContext('2d').drawImage(img, 0, 0, w, h);
            URL.revokeObjectURL(url);
            c.toBlob((b) => resolve(b ? new File([b], file.name.replace(/\.\w+$/, '.jpg'), { type: 'image/jpeg' }) : file), 'image/jpeg', 0.8);
        };
        img.onerror = () => resolve(file);
        img.src = url;
    });
    input.addEventListener('change', async () => {
        let files = [...(input.files || [])].slice(0, 12);
        if (!files.length) { preview.innerHTML = ''; status.textContent = 'Belum ada foto dipilih.'; return; }
        submit.disabled = true;
        submit.textContent = 'Mengompresi foto…';
        status.textContent = 'Mengompresi ' + files.length + ' foto…';
        preview.innerHTML = '';
        const out = [];
        let total = 0;
        for (const f of files) {
            const c = await compress(f);
            out.push(c); total += c.size;
            const u = URL.createObjectURL(c);
            const im = document.createElement('img');
            im.src = u; im.alt = 'Pratinjau ' + c.name; im.loading = 'lazy';
            im.className = 'h-24 w-full rounded-lg object-cover border border-slate-200';
            preview.appendChild(im);
        }
        const dt = new DataTransfer();
        out.forEach((f) => dt.items.add(f));
        input.files = dt.files;
        status.textContent = out.length + ' foto siap (' + fmt(total) + ')' + (out.length < 4 ? ' — minimal 4 foto ya.' : ' — sudah memenuhi minimal.');
        status.className = 'mt-2 text-xs font-semibold ' + (out.length < 4 ? 'text-amber-700' : 'text-emerald-700');
        submit.disabled = false;
        submit.textContent = 'Kirim Check-in';
    });
});
</script>
</body>
</html>

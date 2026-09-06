<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tanda Tangan Kontrak {{ $contract->contract_number }} — RentalMobil</title>
    <meta name="robots" content="noindex">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={theme:{extend:{fontFamily:{sans:['Instrument Sans','ui-sans-serif','sans-serif']}}}}</script>
</head>
<body class="bg-slate-100 font-sans text-slate-800 antialiased">
<div class="mx-auto max-w-3xl px-4 py-8">
    @if (session('status'))
        <div class="mb-4 rounded-xl bg-emerald-50 p-4 text-sm font-semibold text-emerald-800">{{ session('status') }}</div>
    @endif
    @if (session('otp_dev_code'))
        <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">Mode demo — kode OTP Anda: <b class="font-mono text-lg">{{ session('otp_dev_code') }}</b></div>
    @endif

    <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
        <div class="border-b border-slate-100 bg-gradient-to-r from-blue-900 to-blue-700 p-6 text-white">
            <div class="font-mono text-xs uppercase tracking-[.2em] text-blue-200">Kontrak Sewa Kendaraan</div>
            <h1 class="mt-1 text-2xl font-extrabold">{{ $contract->contract_number }}</h1>
            <p class="mt-1 text-sm text-blue-100">Mohon periksa rincian lalu tanda tangan secara elektronik.</p>
        </div>

        <div class="grid gap-4 p-6 sm:grid-cols-2">
            <div><div class="text-xs font-bold uppercase text-slate-400">Penyewa</div><div class="font-bold">{{ $contract->customer?->name }}</div></div>
            <div><div class="text-xs font-bold uppercase text-slate-400">Kendaraan</div><div class="font-bold">{{ $contract->vehicle?->name }} · {{ $contract->vehicle?->plate_number }}</div></div>
            <div><div class="text-xs font-bold uppercase text-slate-400">Periode Sewa</div><div class="font-bold">{{ $contract->start_date?->format('d M Y') }} → {{ $contract->end_date?->format('d M Y') }}</div></div>
            <div><div class="text-xs font-bold uppercase text-slate-400">Total Sewa</div><div class="font-bold">Rp {{ number_format((float) $contract->total_amount, 0, ',', '.') }}</div></div>
            <div><div class="text-xs font-bold uppercase text-slate-400">Deposit</div><div class="font-bold">Rp {{ number_format((float) $contract->deposit_amount, 0, ',', '.') }}</div></div>
            <div><div class="text-xs font-bold uppercase text-slate-400">Batas KM / BBM</div><div class="font-bold">{{ $contract->km_limit ? number_format((float) $contract->km_limit).' km' : 'Tanpa batas' }} · {{ ucfirst($contract->fuel_policy ?? '-') }}</div></div>
        </div>

        <details class="mx-6 mb-4 rounded-xl border border-slate-200 p-4" open>
            <summary class="cursor-pointer text-sm font-bold">Ketentuan pokok sewa</summary>
            <ul class="mt-3 list-disc space-y-1 pl-5 text-sm text-slate-600">
                <li>Penggunaan kendaraan sesuai peruntukan dan area yang disepakati ({{ $contract->usage_area ?? 'sesuai kesepakatan' }}).</li>
                <li>Keterlambatan pengembalian dikenakan biaya sesuai tarif yang berlaku.</li>
                <li>Kerusakan/kehilangan di luar pemakaian wajar menjadi tanggung jawab penyewa ({{ $contract->damage_policy ?? 'sesuai kontrak' }}).</li>
                <li>Asuransi: {{ $contract->insurance_policy ?? 'menurut polis kendaraan' }}.</li>
                <li>Deposit dikembalikan setelah pemeriksaan kondisi kendaraan saat pengembalian.</li>
            </ul>
        </details>

        <form method="post" action="{{ route('handover.contract.sign', $token) }}" class="border-t border-slate-100 p-6" id="sign-form">
            @csrf
            @if ($otpRequired)
                <div class="mb-5 rounded-xl bg-blue-50 p-4">
                    <label class="block text-sm font-bold text-blue-900">Verifikasi OTP</label>
                    <p class="mt-1 text-xs text-blue-700">Masukkan 6 digit kode yang dikirim ke nomor/email Anda.</p>
                    <div class="mt-2 flex gap-2">
                        <input name="otp" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required placeholder="••••••" class="w-32 rounded-lg border border-blue-300 px-3 py-2 font-mono text-lg tracking-[.3em]">
                        <button type="button" data-otp-btn data-otp-url="{{ route('handover.contract.otp', $token) }}" class="rounded-lg border border-blue-300 px-4 text-sm font-bold text-blue-700 hover:bg-blue-100 disabled:opacity-60">Kirim OTP</button>
                    </div>
                    <p class="hidden mt-2 text-xs font-semibold text-blue-700" data-otp-info aria-live="polite"></p>
                </div>
            @endif

            <label class="block text-sm font-bold">Tanda tangan elektronik</label>
            <p class="mt-1 text-xs text-slate-500">Tanda tangan ini sah menurut UU ITE — tercatat waktu, alamat IP, dan sidik jari dokumen (SHA-256).</p>
            <canvas id="pad" width="640" height="220" class="mt-3 w-full touch-none rounded-xl border-2 border-dashed border-slate-300 bg-slate-50"></canvas>
            <p id="sign-error" class="hidden mt-2 text-sm font-semibold text-red-600" role="alert">Mohon tanda tangan dulu sebelum mengirim.</p>
            <input type="hidden" name="signature" id="signature-input">
            <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
                <button type="button" id="clear" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-bold text-slate-600 hover:bg-slate-50">Hapus</button>
                <button id="submit-btn" class="rounded-xl bg-blue-700 px-8 py-3 font-extrabold text-white shadow-lg shadow-blue-700/25 transition hover:bg-blue-800 disabled:opacity-50">Tanda Tangan &amp; Kirim</button>
            </div>
        </form>
    </div>
    <p class="mt-4 text-center text-xs text-slate-400">© {{ date('Y') }} RentalMobil — dokumen elektronik.</p>
</div>

<script>
(function () {
    var canvas = document.getElementById('pad');
    var ctx = canvas.getContext('2d');
    // HiDPI: samakan resolusi canvas dengan tampilan agar tidak blur.
    (function fit() {
        var ratio = Math.min(2, window.devicePixelRatio || 1);
        var w = canvas.clientWidth || 640;
        var h = 220;
        canvas.width = Math.round(w * ratio);
        canvas.height = Math.round(h * ratio);
        ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
        ctx.lineWidth = 2.6;
        ctx.lineCap = 'round';
        ctx.strokeStyle = '#0f172a';
    })();
    var drawing = false, hasInk = false;
    var err = document.getElementById('sign-error');

    function pos(e) {
        var rect = canvas.getBoundingClientRect();
        return [(e.clientX - rect.left) * canvas.width / rect.width, (e.clientY - rect.top) * canvas.height / rect.height];
    }
    function start(e) { drawing = true; hasInk = true; ctx.beginPath(); ctx.moveTo.apply(ctx, pos(e)); e.preventDefault(); }
    function move(e) { if (!drawing) return; ctx.lineTo.apply(ctx, pos(e)); ctx.stroke(); e.preventDefault(); }
    function end() { drawing = false; }

    ['mousedown','touchstart'].forEach(function(ev){canvas.addEventListener(ev,start,{passive:false});});
    ['mousemove','touchmove'].forEach(function(ev){canvas.addEventListener(ev,move,{passive:false});});
    ['mouseup','mouseleave','touchend','touchcancel'].forEach(function(ev){canvas.addEventListener(ev,end);});

    document.getElementById('clear').addEventListener('click', function () {
        ctx.save();
        ctx.setTransform(1, 0, 0, 1, 0, 0);
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        ctx.restore();
        hasInk = false;
        if (err) err.classList.add('hidden');
    });

    document.getElementById('sign-form').addEventListener('submit', function (e) {
        if (!hasInk) {
            e.preventDefault();
            if (err) err.classList.remove('hidden');
            canvas.focus();
            return;
        }
        document.getElementById('signature-input').value = canvas.toDataURL('image/png');
        var btn = document.getElementById('submit-btn');
        if (btn) { btn.disabled = true; btn.textContent = 'Mengirim…'; }
    });

    var otpBtn = document.querySelector('[data-otp-btn]');
    if (otpBtn) {
        otpBtn.addEventListener('click', function () {
            var info = document.querySelector('[data-otp-info]');
            otpBtn.disabled = true;
            otpBtn.textContent = 'Mengirim…';
            fetch(otpBtn.dataset.otpUrl, { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } })
                .then(function (r) { if (!r.ok) throw 0; return r.json().catch(function () { return {}; }); })
                .then(function () {
                    if (info) { info.textContent = 'OTP terkirim — cek SMS/email, lalu masukkan 6 digit.'; info.classList.remove('hidden'); }
                    otpBtn.textContent = 'Kirim ulang OTP';
                })
                .catch(function () {
                    if (info) { info.textContent = 'Gagal mengirim OTP — coba lagi.'; info.classList.remove('hidden'); }
                    otpBtn.textContent = 'Coba lagi';
                })
                .finally(function () { otpBtn.disabled = false; });
        });
    }
})();
</script>
</body>
</html>

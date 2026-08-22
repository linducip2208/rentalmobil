<div class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-center">
    <div id="tte-qr" class="mx-auto inline-block"></div>
    <p class="mt-2 text-xs text-slate-500">Scan untuk membuka halaman tanda tangan di HP penyewa.</p>
</div>
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var el = document.getElementById('tte-qr');
        var input = el && el.closest('[x-data], form, .fi-modal') ? document.querySelector('input[name="link"]') : null;
        var link = input ? input.value : window.location.href;
        if (el && window.QRCode) {
            el.innerHTML = '';
            new QRCode(el, { text: link, width: 180, height: 180, correctLevel: QRCode.CorrectLevel.M });
        }
    });
</script>

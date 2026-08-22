<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kontrak Ditandatangani — RentalMobil</title><meta name="robots" content="noindex">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="grid min-h-screen place-items-center bg-slate-100 font-sans text-slate-800">
<div class="mx-4 w-full max-w-md rounded-2xl bg-white p-8 text-center shadow-sm">
    <div class="mx-auto grid h-16 w-16 place-items-center rounded-full bg-emerald-100 text-3xl">✅</div>
    <h1 class="mt-4 text-2xl font-extrabold">Kontrak berhasil ditandatangani</h1>
    <p class="mt-2 text-sm text-slate-500">Nomor kontrak <b class="font-mono">{{ $contract->contract_number }}</b> sudah sah secara elektronik. Salinan akan dikirim oleh tim kami.</p>
    <dl class="mt-5 space-y-2 rounded-xl bg-slate-50 p-4 text-left text-xs">
        <div class="flex justify-between"><dt class="text-slate-400">Ditandatangani</dt><dd class="font-bold">{{ $contract->signed_at?->format('d M Y H:i') }}</dd></div>
        <div class="flex justify-between gap-4"><dt class="shrink-0 text-slate-400">Sidik jari dokumen</dt><dd class="truncate font-mono" title="{{ $contract->document_hash }}">{{ substr($contract->document_hash, 0, 24) }}…</dd></div>
    </dl>
    <a href="/" class="mt-6 inline-block rounded-xl bg-blue-700 px-6 py-3 font-bold text-white">Selesai</a>
</div>
</body>
</html>

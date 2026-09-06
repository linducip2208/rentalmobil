@extends('portal.layout')
@section('title','Invoice Saya')
@section('content')
<h1 class="text-3xl font-black">Invoice saya</h1>
<p class="mt-2 text-slate-500">Unduh invoice atau kirim bukti pembayaran secara aman.</p>
@if(session('status'))<div class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-800">{{ session('status') }}</div>@endif
<div class="mt-7 grid gap-4">
@forelse($invoices as $i)
<article class="lift rounded-2xl border border-slate-200 bg-white p-5">
  <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
    <div><p class="font-mono text-sm font-bold text-blue-600">{{ $i->invoice_number }}</p><p class="mt-1 text-sm text-slate-500">Jatuh tempo {{ $i->due_date?->format('d M Y') }}</p></div>
    <div class="sm:text-right"><strong class="text-xl">Rp {{ number_format($i->balance_due,0,',','.') }}</strong><p class="mt-1"><x-portal.status-badge :status="$i->status" type="invoice" /></p></div>
  </div>
  <div class="mt-4 flex flex-col gap-3 border-t border-slate-100 pt-4 sm:flex-row">
    <a class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-300 px-4 text-sm font-bold hover:bg-slate-50" href="{{ route('portal.invoices.download',$i) }}">Unduh PDF</a>
    @if((float)$i->balance_due > 0)
    @if($paymentProviders->isNotEmpty())<form method="post" action="{{ route('portal.invoices.pay',$i) }}" class="flex flex-1 gap-2">@csrf<select name="provider_id" required class="min-h-11 flex-1 rounded-xl border px-3">@foreach($paymentProviders as $provider)<option value="{{ $provider->id }}">{{ $provider->name }}</option>@endforeach</select><button class="rounded-xl bg-emerald-600 px-4 font-bold text-white">Bayar online</button></form>@endif
    <details class="flex-1 rounded-xl bg-slate-50 p-3"><summary class="cursor-pointer text-sm font-bold text-blue-700">Kirim bukti pembayaran</summary>
      <form class="mt-4 grid gap-3 sm:grid-cols-2" method="post" enctype="multipart/form-data" action="{{ route('portal.invoices.payment-proof',$i) }}" data-proof-form>@csrf
        <input class="min-h-11 rounded-lg border px-3" name="amount" type="number" min="1" max="{{ $i->balance_due }}" placeholder="Nominal" required>
        <input class="min-h-11 rounded-lg border px-3" name="reference_number" placeholder="Nomor referensi">
        <input class="rounded-lg border bg-white p-2 sm:col-span-2" name="proof" type="file" accept=".jpg,.jpeg,.png,.pdf" required data-proof-input>
        <p class="hidden text-xs font-semibold text-slate-500 sm:col-span-2" data-proof-info aria-live="polite"></p>
        <button class="min-h-11 rounded-lg bg-blue-600 px-4 font-bold text-white disabled:opacity-60 sm:col-span-2" data-proof-submit>Kirim untuk diverifikasi</button>
      </form>
    </details>
    @endif
  </div>
</article>
@empty<div class="rounded-2xl border bg-white p-10 text-center text-slate-500">Belum ada invoice.</div>@endforelse
</div><div class="mt-6">{{ $invoices->links() }}</div>
<script>
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-proof-form]').forEach((form) => {
    const input = form.querySelector('[data-proof-input]');
    const info = form.querySelector('[data-proof-info]');
    const submit = form.querySelector('[data-proof-submit]');
    input?.addEventListener('change', () => {
      const f = input.files?.[0];
      if (!f) return;
      if (f.size > 4 * 1024 * 1024) {
        info.textContent = 'Maks 4MB — file Anda ' + (f.size / 1048576).toFixed(1) + 'MB. Kecilkan dulu ya.';
        info.classList.remove('hidden');
        input.value = '';
        return;
      }
      info.textContent = 'Terpilih: ' + f.name + ' (' + Math.max(1, Math.round(f.size / 1024)) + ' KB)';
      info.classList.remove('hidden');
    });
    form.addEventListener('submit', () => {
      submit.disabled = true;
      submit.textContent = 'Mengirim…';
    });
  });
});
</script>
@endsection

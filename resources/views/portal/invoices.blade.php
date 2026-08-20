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
    <div class="sm:text-right"><strong class="text-xl">Rp {{ number_format($i->balance_due,0,',','.') }}</strong><p class="mt-1 text-xs font-bold uppercase tracking-wider text-slate-500">{{ str_replace('_',' ',$i->status) }}</p></div>
  </div>
  <div class="mt-4 flex flex-col gap-3 border-t border-slate-100 pt-4 sm:flex-row">
    <a class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-300 px-4 text-sm font-bold hover:bg-slate-50" href="{{ route('portal.invoices.download',$i) }}">Unduh PDF</a>
    @if((float)$i->balance_due > 0)
    <details class="flex-1 rounded-xl bg-slate-50 p-3"><summary class="cursor-pointer text-sm font-bold text-blue-700">Kirim bukti pembayaran</summary>
      <form class="mt-4 grid gap-3 sm:grid-cols-2" method="post" enctype="multipart/form-data" action="{{ route('portal.invoices.payment-proof',$i) }}">@csrf
        <input class="min-h-11 rounded-lg border px-3" name="amount" type="number" min="1" max="{{ $i->balance_due }}" placeholder="Nominal" required>
        <input class="min-h-11 rounded-lg border px-3" name="reference_number" placeholder="Nomor referensi">
        <input class="rounded-lg border bg-white p-2 sm:col-span-2" name="proof" type="file" accept=".jpg,.jpeg,.png,.pdf" required>
        <button class="min-h-11 rounded-lg bg-blue-600 px-4 font-bold text-white sm:col-span-2">Kirim untuk diverifikasi</button>
      </form>
    </details>
    @endif
  </div>
</article>
@empty<div class="rounded-2xl border bg-white p-10 text-center text-slate-500">Belum ada invoice.</div>@endforelse
</div><div class="mt-6">{{ $invoices->links() }}</div>
@endsection

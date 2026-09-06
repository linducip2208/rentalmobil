<x-filament::page>
<div class="space-y-6" wire:poll.30s>
<div class="relative overflow-hidden rounded-2xl bg-slate-950 p-7 text-white">
<div class="absolute inset-y-0 right-10 w-px bg-amber-400/60"></div>
<p class="font-mono text-xs uppercase tracking-[.24em] text-amber-300">Live operations · diperbarui otomatis 30 dtk</p>
<h2 class="mt-2 text-3xl font-black">Yang membutuhkan tindakan sekarang</h2>
<p class="mt-2 max-w-2xl text-slate-300">Prioritas lintas rental, pembayaran, tracker, servis, dan dokumen dalam satu layar. Total perlu tindakan: <strong class="text-white">{{ $this->totalUrgent() }}</strong> · terakhir dicek {{ now()->format('H:i:s') }}.</p>
@if($this->totalUrgent() === 0)
<p class="mt-4 inline-flex items-center gap-2 rounded-xl bg-emerald-500/15 px-4 py-2 text-sm font-bold text-emerald-300">✓ Semua operasional normal — tidak ada antrean mendesak.</p>
@endif
</div>
<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
@foreach($this->alerts() as $alert)
<a href="{{ $alert['url'] }}" class="group rounded-2xl border p-6 transition hover:-translate-y-1 hover:shadow-xl {{ $alert['value'] > 0 ? 'border-slate-200 bg-white' : 'border-emerald-100 bg-emerald-50/40' }}">
<div class="flex items-center justify-between">
<span class="text-sm font-semibold text-slate-500">{{ $alert['label'] }}</span>
<span class="h-2.5 w-2.5 rounded-full {{ $alert['dot'] }}"></span>
</div>
<strong class="mt-5 block text-4xl font-black {{ $alert['value'] > 0 ? 'text-slate-950' : 'text-emerald-700' }}">{{ $alert['value'] }}</strong>
<p class="mt-1 text-xs text-slate-400">{{ $alert['hint'] ?? '' }}</p>
<span class="mt-5 inline-block text-sm font-bold text-blue-600">Buka daftar →</span>
</a>
@endforeach
</div>
</div>
</x-filament::page>

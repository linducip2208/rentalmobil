@php($data = $this->getCalendarData())
<x-filament::page>
<div class="space-y-6">
    <div class="overflow-hidden rounded-2xl bg-slate-950 p-6 text-white shadow-xl"><p class="font-mono text-xs uppercase tracking-[.22em] text-blue-300">14 hari ke depan</p><h2 class="mt-2 text-2xl font-extrabold">Jalur penggunaan armada</h2><p class="mt-2 text-sm text-slate-300">Lihat benturan jadwal, serah-terima, dan kendaraan yang segera kembali.</p></div>
    <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-[1100px] w-full text-xs"><thead><tr class="bg-slate-50"><th class="sticky left-0 z-10 min-w-48 bg-slate-50 p-3 text-left">Kendaraan / Customer</th>@foreach($data['days'] as $day)<th class="min-w-20 p-3 text-center {{ $day->isToday() ? 'bg-blue-50 text-blue-700' : '' }}"><span class="block font-mono">{{ $day->format('D') }}</span><strong>{{ $day->format('d M') }}</strong></th>@endforeach</tr></thead>
        <tbody class="divide-y">@forelse($data['orders'] as $order)<tr><td class="sticky left-0 z-10 bg-white p-3"><strong class="block">{{ $order->vehicle?->name }}</strong><span class="text-slate-500">{{ $order->customer?->name }}</span></td>@foreach($data['days'] as $day)@php($active = $day->between($order->start_date->startOfDay(), $order->end_date->endOfDay()))<td class="p-1"><div title="{{ $order->order_number }}" class="h-9 rounded-lg {{ $active ? ($order->status === 'overdue' ? 'bg-red-500' : 'bg-blue-600') : 'bg-slate-50' }}">@if($active && $day->isSameDay($order->start_date))<span class="block truncate px-2 py-2 font-bold text-white">{{ $order->order_number }}</span>@endif</div></td>@endforeach</tr>@empty<tr><td colspan="15" class="p-10 text-center text-slate-500">Tidak ada jadwal aktif dalam 14 hari ke depan.</td></tr>@endforelse</tbody></table>
    </div>
</div>
</x-filament::page>

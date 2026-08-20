@extends('portal.layout')
@section('content')
@php $pageTitle = 'Pesanan Saya'; @endphp

<div class="mb-6">
    <h1 class="text-2xl font-bold text-stone-900">Pesanan Saya</h1>
    <p class="text-stone-500 text-sm mt-1">Daftar seluruh pesanan rental Anda.</p>
</div>

<div class="flex flex-wrap gap-2 mb-6">
    @php
        $statuses = ['all' => 'Semua', 'pending' => 'Pending', 'confirmed' => 'Dikonfirmasi', 'active' => 'Aktif', 'completed' => 'Selesai', 'cancelled' => 'Dibatalkan'];
        $current = $status ?? 'all';
    @endphp
    @foreach ($statuses as $key => $label)
        <a href="{{ route('portal.orders.index', $key === 'all' ? [] : ['status' => $key]) }}"
           class="px-4 py-1.5 rounded-lg text-sm font-medium transition {{ $current === $key ? 'bg-blue-600 text-white shadow-sm' : 'bg-white text-stone-600 border border-stone-200 hover:bg-stone-50' }}">
            {{ $label }}
        </a>
    @endforeach
</div>

<div class="bg-white rounded-xl border border-stone-200 overflow-hidden">
    @if ($orders->isEmpty())
        <div class="p-8 text-center text-stone-400 text-sm">Belum ada pesanan.</div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-stone-100 text-left text-xs font-semibold text-stone-500 uppercase tracking-wider">
                        <th class="px-5 py-3">Nomor</th>
                        <th class="px-5 py-3">Mobil</th>
                        <th class="px-5 py-3">Tanggal</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Total</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($orders as $order)
                        @php
                            $statusColors = [
                                'pending' => 'bg-amber-50 text-amber-700',
                                'confirmed' => 'bg-blue-50 text-blue-700',
                                'dispatched' => 'bg-indigo-50 text-indigo-700',
                                'active' => 'bg-emerald-50 text-emerald-700',
                                'return_pending' => 'bg-orange-50 text-orange-700',
                                'returned' => 'bg-cyan-50 text-cyan-700',
                                'completed' => 'bg-green-50 text-green-700',
                                'cancelled' => 'bg-red-50 text-red-700',
                            ];
                            $labels = [
                                'pending' => 'Pending', 'confirmed' => 'Dikonfirmasi', 'dispatched' => 'Dikirim',
                                'active' => 'Aktif', 'return_pending' => 'Return Pending', 'returned' => 'Dikembalikan',
                                'completed' => 'Selesai', 'cancelled' => 'Dibatalkan',
                            ];
                        @endphp
                        <tr class="border-b border-stone-50 hover:bg-stone-50 transition">
                            <td class="px-5 py-3 font-medium text-stone-900">{{ $order->order_number }}</td>
                            <td class="px-5 py-3 text-stone-600">{{ $order->vehicle->name ?? '-' }}</td>
                            <td class="px-5 py-3 text-stone-500">{{ $order->start_date->format('d M Y') }} - {{ $order->end_date->format('d M Y') }}</td>
                            <td class="px-5 py-3">
                                <span class="inline-flex px-2.5 py-0.5 rounded-md text-xs font-semibold {{ $statusColors[$order->status] ?? 'bg-stone-100 text-stone-600' }}">
                                    {{ $labels[$order->status] ?? $order->status }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right font-medium text-stone-900">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                            <td class="px-5 py-3 text-right">
                                <a href="{{ route('portal.orders.show', $order) }}" class="text-blue-600 text-sm font-medium hover:underline">Detail</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-5 py-3">{{ $orders->links() }}</div>
    @endif
</div>
@endsection

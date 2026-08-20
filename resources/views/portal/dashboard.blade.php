@extends('portal.layout')
@section('content')
@php $pageTitle = 'Dashboard'; @endphp

<div class="mb-8">
    <h1 class="text-2xl font-bold text-stone-900">Selamat datang, {{ $customer->name }}!</h1>
    <p class="text-stone-500 text-sm mt-1">Kelola booking, pesanan, dan pembayaran Anda.</p>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="bg-white rounded-xl border border-stone-200 p-5 hover:shadow-md transition">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0H21a.75.75 0 00.75-.75V11.25a3 3 0 00-3-3h-1.5l-1.72-4.57A1.5 1.5 0 0013.1 2H10.9a1.5 1.5 0 00-1.43 1.03L7.75 8.25H6.75a3 3 0 00-3 3v7.125c0 .621.504 1.125 1.125 1.125h.75"/></svg>
            </div>
            <span class="text-sm text-stone-500">Pesanan Aktif</span>
        </div>
        <div class="text-3xl font-bold text-stone-900">{{ $stats['active_orders'] }}</div>
    </div>

    <div class="bg-white rounded-xl border border-stone-200 p-5 hover:shadow-md transition">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-600">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <span class="text-sm text-stone-500">Total Pengeluaran</span>
        </div>
        <div class="text-3xl font-bold text-stone-900">Rp {{ number_format($stats['total_spent'], 0, ',', '.') }}</div>
    </div>

    <div class="bg-white rounded-xl border border-stone-200 p-5 hover:shadow-md transition">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-lg bg-amber-50 flex items-center justify-center text-amber-600">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
            </div>
            <span class="text-sm text-stone-500">Trust Score</span>
        </div>
        <div class="text-3xl font-bold text-stone-900">{{ $stats['trust_score'] }}<span class="text-lg text-stone-400">/100</span></div>
    </div>

    <div class="bg-white rounded-xl border border-stone-200 p-5 hover:shadow-md transition">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-lg bg-violet-50 flex items-center justify-center text-violet-600">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"/></svg>
            </div>
            <span class="text-sm text-stone-500">Loyalty Tier</span>
        </div>
        <div class="text-3xl font-bold text-stone-900">{{ $stats['loyalty_tier'] }}</div>
    </div>
</div>

<div class="flex flex-col sm:flex-row gap-3 mb-8">
    <a href="{{ route('portal.bookings.create') }}"
       class="inline-flex items-center justify-center gap-2 bg-blue-600 text-white px-5 py-2.5 rounded-lg text-sm font-medium hover:bg-blue-700 transition shadow-sm">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
        Booking Mobil
    </a>
    <a href="{{ route('portal.orders.index') }}"
       class="inline-flex items-center justify-center gap-2 bg-white text-stone-700 border border-stone-300 px-5 py-2.5 rounded-lg text-sm font-medium hover:bg-stone-50 transition">
        Lihat Pesanan
    </a>
</div>

<div class="bg-white rounded-xl border border-stone-200 overflow-hidden">
    <div class="px-5 py-4 border-b border-stone-100">
        <h2 class="font-semibold text-stone-900">Pesanan Terbaru</h2>
    </div>
    @if ($recentOrders->isEmpty())
        <div class="p-8 text-center text-stone-400 text-sm">
            Belum ada pesanan. <a href="{{ route('portal.bookings.create') }}" class="text-blue-600 font-medium hover:underline">Buat booking pertama Anda.</a>
        </div>
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
                    </tr>
                </thead>
                <tbody>
                    @foreach ($recentOrders as $order)
                        <tr class="border-b border-stone-50 hover:bg-stone-50 transition">
                            <td class="px-5 py-3 font-medium text-stone-900">
                                <a href="{{ route('portal.orders.show', $order) }}" class="hover:text-blue-600">{{ $order->order_number }}</a>
                            </td>
                            <td class="px-5 py-3 text-stone-600">{{ $order->vehicle->name ?? '-' }}</td>
                            <td class="px-5 py-3 text-stone-500">{{ $order->start_date->format('d M Y') }} - {{ $order->end_date->format('d M Y') }}</td>
                            <td class="px-5 py-3">
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
                                        'pending' => 'Pending',
                                        'confirmed' => 'Dikonfirmasi',
                                        'dispatched' => 'Dikirim',
                                        'active' => 'Aktif',
                                        'return_pending' => 'Return Pending',
                                        'returned' => 'Dikembalikan',
                                        'completed' => 'Selesai',
                                        'cancelled' => 'Dibatalkan',
                                    ];
                                @endphp
                                <span class="inline-flex px-2.5 py-0.5 rounded-md text-xs font-semibold {{ $statusColors[$order->status] ?? 'bg-stone-100 text-stone-600' }}">
                                    {{ $labels[$order->status] ?? $order->status }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right font-medium text-stone-900">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection

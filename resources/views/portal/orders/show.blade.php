@extends('portal.layout')
@section('content')
@php $pageTitle = 'Detail Pesanan'; @endphp

<div class="mb-6">
    <a href="{{ route('portal.orders.index') }}" class="text-sm text-blue-600 hover:underline inline-flex items-center gap-1 mb-2">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
        Kembali ke Pesanan
    </a>
    <div class="flex flex-wrap items-center gap-3">
        <h1 class="text-2xl font-bold text-stone-900">{{ $order->order_number }}</h1>
        @php
            $statusColors = [
                'pending' => 'bg-amber-50 text-amber-700', 'confirmed' => 'bg-blue-50 text-blue-700',
                'dispatched' => 'bg-indigo-50 text-indigo-700', 'active' => 'bg-emerald-50 text-emerald-700',
                'return_pending' => 'bg-orange-50 text-orange-700', 'returned' => 'bg-cyan-50 text-cyan-700',
                'completed' => 'bg-green-50 text-green-700', 'cancelled' => 'bg-red-50 text-red-700',
            ];
            $labels = [
                'pending' => 'Pending', 'confirmed' => 'Dikonfirmasi', 'dispatched' => 'Dikirim',
                'active' => 'Aktif', 'return_pending' => 'Return Pending', 'returned' => 'Dikembalikan',
                'completed' => 'Selesai', 'cancelled' => 'Dibatalkan',
            ];
        @endphp
        <span class="inline-flex px-2.5 py-0.5 rounded-md text-xs font-semibold {{ $statusColors[$order->status] ?? '' }}">
            {{ $labels[$order->status] ?? $order->status }}
        </span>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-xl border border-stone-200 p-5">
            <h2 class="font-semibold text-stone-900 mb-4">Informasi Kendaraan</h2>
            <div class="flex items-start gap-4">
                @if ($order->vehicle->photo_url)
                    <img src="{{ asset('storage/' . $order->vehicle->photo_url) }}" alt="{{ $order->vehicle->name }}" class="w-24 h-24 rounded-lg object-cover">
                @else
                    <div class="w-24 h-24 rounded-lg bg-stone-100 flex items-center justify-center text-3xl text-stone-300">&#128663;</div>
                @endif
                <div>
                    <h3 class="font-bold text-stone-900">{{ $order->vehicle->name }}</h3>
                    <p class="text-sm text-stone-500">{{ $order->vehicle->brand->name ?? '' }} {{ $order->vehicle->category->name ?? '' }}</p>
                    <p class="text-sm text-stone-500">{{ $order->vehicle->plate_number }} &middot; {{ $order->vehicle->year }} &middot; {{ ucfirst($order->vehicle->transmission) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-stone-200 p-5">
            <h2 class="font-semibold text-stone-900 mb-4">Periode Rental</h2>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-stone-500">Tanggal Mulai</p>
                    <p class="font-medium text-stone-900">{{ $order->start_date->format('d M Y') }}</p>
                </div>
                <div>
                    <p class="text-stone-500">Tanggal Selesai</p>
                    <p class="font-medium text-stone-900">{{ $order->end_date->format('d M Y') }}</p>
                </div>
                @if ($order->actual_return_date)
                <div>
                    <p class="text-stone-500">Pengembalian Aktual</p>
                    <p class="font-medium text-stone-900">{{ $order->actual_return_date->format('d M Y') }}</p>
                </div>
                @endif
                <div>
                    <p class="text-stone-500">Durasi</p>
                    <p class="font-medium text-stone-900">{{ $order->duration_days }} hari</p>
                </div>
                @if ($order->driver)
                <div>
                    <p class="text-stone-500">Driver</p>
                    <p class="font-medium text-stone-900">{{ $order->driver->name }}</p>
                </div>
                @endif
            </div>
        </div>

        @if ($order->items->isNotEmpty())
        <div class="bg-white rounded-xl border border-stone-200 p-5">
            <h2 class="font-semibold text-stone-900 mb-4">Addons</h2>
            <div class="space-y-2">
                @foreach ($order->items as $item)
                    <div class="flex justify-between text-sm">
                        <span class="text-stone-600">{{ $item->addon->name ?? $item->name }} ({{ $item->quantity }}x)</span>
                        <span class="font-medium text-stone-900">Rp {{ number_format($item->total_price, 0, ',', '.') }}</span>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        <div class="bg-white rounded-xl border border-stone-200 p-5">
            <h2 class="font-semibold text-stone-900 mb-4">Timeline</h2>
            <div class="space-y-4">
                @foreach ($timeline as $event)
                    <div class="flex gap-3">
                        <div class="flex flex-col items-center">
                            <div class="w-3 h-3 rounded-full {{ $event['done'] ? 'bg-blue-600' : 'bg-stone-300' }}"></div>
                            @if (!$loop->last)
                                <div class="w-0.5 flex-1 bg-stone-200 mt-1"></div>
                            @endif
                        </div>
                        <div class="pb-4">
                            <p class="text-sm font-medium text-stone-900">{{ $event['label'] }}</p>
                            <p class="text-xs text-stone-500">{{ $event['date'] ? $event['date']->format('d M Y H:i') : '-' }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <div class="bg-white rounded-xl border border-stone-200 p-5">
            <h2 class="font-semibold text-stone-900 mb-4">Ringkasan Pembayaran</h2>
            <div class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-stone-500">Subtotal</span>
                    <span class="text-stone-900">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
                </div>
                @if ($order->addon_total > 0)
                <div class="flex justify-between">
                    <span class="text-stone-500">Addons</span>
                    <span class="text-stone-900">Rp {{ number_format($order->addon_total, 0, ',', '.') }}</span>
                </div>
                @endif
                @if ($order->discount_total > 0)
                <div class="flex justify-between text-emerald-600">
                    <span>Diskon</span>
                    <span>-Rp {{ number_format($order->discount_total, 0, ',', '.') }}</span>
                </div>
                @endif
                <div class="flex justify-between">
                    <span class="text-stone-500">Pajak (11%)</span>
                    <span class="text-stone-900">Rp {{ number_format($order->tax_amount, 0, ',', '.') }}</span>
                </div>
                @if ($order->late_fee > 0)
                <div class="flex justify-between text-red-600">
                    <span>Denda Keterlambatan</span>
                    <span>Rp {{ number_format($order->late_fee, 0, ',', '.') }}</span>
                </div>
                @endif
                @if ($order->damage_fee > 0)
                <div class="flex justify-between text-red-600">
                    <span>Denda Kerusakan</span>
                    <span>Rp {{ number_format($order->damage_fee, 0, ',', '.') }}</span>
                </div>
                @endif
                <div class="border-t border-stone-200 pt-3 flex justify-between font-bold">
                    <span class="text-stone-900">Total</span>
                    <span class="text-stone-900">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-stone-500">Sudah Dibayar</span>
                    <span class="text-emerald-600 font-medium">Rp {{ number_format($order->amount_paid, 0, ',', '.') }}</span>
                </div>
                @if ($order->balance_due > 0)
                <div class="flex justify-between">
                    <span class="text-stone-500">Sisa Tagihan</span>
                    <span class="text-red-600 font-bold">Rp {{ number_format($order->balance_due, 0, ',', '.') }}</span>
                </div>
                @endif
            </div>
        </div>

        @if ($order->payments->isNotEmpty())
        <div class="bg-white rounded-xl border border-stone-200 p-5">
            <h2 class="font-semibold text-stone-900 mb-4">Riwayat Pembayaran</h2>
            <div class="space-y-3">
                @foreach ($order->payments as $payment)
                    <div class="flex justify-between items-start text-sm">
                        <div>
                            <p class="font-medium text-stone-900">{{ $payment->payment_number }}</p>
                            <p class="text-xs text-stone-500">{{ $payment->payment_date->format('d M Y') }} &middot; {{ $payment->paymentMethod->name ?? '-' }}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-medium text-stone-900">Rp {{ number_format($payment->amount, 0, ',', '.') }}</p>
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-semibold {{ $payment->status === 'verified' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                {{ ucfirst($payment->status) }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

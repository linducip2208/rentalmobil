@extends('portal.layout')
@section('content')
@php $pageTitle = 'Detail Booking ' . $booking->booking_number; @endphp

<div class="mb-6">
    <a href="{{ route('portal.bookings.index') }}" class="text-sm text-blue-600 hover:underline inline-flex items-center gap-1 mb-2">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
        Kembali ke Booking
    </a>
    <div class="flex flex-wrap items-center gap-3">
        <h1 class="text-2xl font-bold text-stone-900">{{ $booking->booking_number }}</h1>
        @php
            $bkStatusColors = [
                'pending' => 'bg-amber-50 text-amber-700', 'confirmed' => 'bg-blue-50 text-blue-700',
                'cancelled' => 'bg-red-50 text-red-700', 'converted_to_order' => 'bg-emerald-50 text-emerald-700',
            ];
            $bkLabels = [
                'pending' => 'Menunggu', 'confirmed' => 'Dikonfirmasi',
                'cancelled' => 'Dibatalkan', 'converted_to_order' => 'Sudah Dipesan',
            ];
        @endphp
        <span class="inline-flex px-2.5 py-0.5 rounded-md text-xs font-semibold {{ $bkStatusColors[$booking->status] ?? '' }}">
            {{ $bkLabels[$booking->status] ?? $booking->status }}
        </span>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-xl border border-stone-200 p-5">
            <h2 class="font-semibold text-stone-900 mb-4">Informasi Kendaraan</h2>
            <div class="flex items-start gap-4">
                @if ($booking->vehicle->photo_url)
                    <img src="{{ asset('storage/' . $booking->vehicle->photo_url) }}" alt="{{ $booking->vehicle->name }}" class="w-24 h-24 rounded-lg object-cover">
                @else
                    <div class="w-24 h-24 rounded-lg bg-stone-100 flex items-center justify-center text-3xl text-stone-300">&#128663;</div>
                @endif
                <div>
                    <h3 class="font-bold text-stone-900">{{ $booking->vehicle->name }}</h3>
                    <p class="text-sm text-stone-500">{{ $booking->vehicle->brand->name ?? '' }} {{ $booking->vehicle->category->name ?? '' }}</p>
                    <p class="text-sm text-stone-500">{{ $booking->vehicle->plate_number }} &middot; {{ $booking->vehicle->year }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-stone-200 p-5">
            <h2 class="font-semibold text-stone-900 mb-4">Detail Booking</h2>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-stone-500">Tanggal Mulai</p>
                    <p class="font-medium text-stone-900">{{ \Carbon\Carbon::parse($booking->start_date)->format('d M Y') }}</p>
                </div>
                <div>
                    <p class="text-stone-500">Tanggal Selesai</p>
                    <p class="font-medium text-stone-900">{{ \Carbon\Carbon::parse($booking->end_date)->format('d M Y') }}</p>
                </div>
                <div>
                    <p class="text-stone-500">Lokasi Pengambilan</p>
                    <p class="font-medium text-stone-900">{{ $booking->pickupLocation->name ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-stone-500">Lokasi Pengembalian</p>
                    <p class="font-medium text-stone-900">{{ $booking->returnLocation->name ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-stone-500">Tipe Rental</p>
                    <p class="font-medium text-stone-900">{{ $booking->rental_type === 'self_drive' ? 'Lepas Kunci' : 'Dengan Driver' }}</p>
                </div>
                @if ($booking->driver)
                <div>
                    <p class="text-stone-500">Driver</p>
                    <p class="font-medium text-stone-900">{{ $booking->driver->name }}</p>
                </div>
                @endif
                @if ($booking->notes)
                <div class="col-span-2">
                    <p class="text-stone-500">Catatan</p>
                    <p class="font-medium text-stone-900">{{ $booking->notes }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <div class="bg-white rounded-xl border border-stone-200 p-5">
            <h2 class="font-semibold text-stone-900 mb-4">Ringkasan Biaya</h2>
            <div class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-stone-500">Subtotal</span>
                    <span class="text-stone-900">Rp {{ number_format($booking->subtotal, 0, ',', '.') }}</span>
                </div>
                @if ($booking->discount_amount > 0)
                <div class="flex justify-between text-emerald-600">
                    <span>Diskon</span>
                    <span>-Rp {{ number_format($booking->discount_amount, 0, ',', '.') }}</span>
                </div>
                @endif
                <div class="flex justify-between">
                    <span class="text-stone-500">Pajak (11%)</span>
                    <span class="text-stone-900">Rp {{ number_format($booking->tax_amount, 0, ',', '.') }}</span>
                </div>
                <div class="border-t border-stone-200 pt-3 flex justify-between font-bold">
                    <span class="text-stone-900">Total</span>
                    <span class="text-stone-900">Rp {{ number_format($booking->total_amount, 0, ',', '.') }}</span>
                </div>
                @if ($booking->deposit_amount > 0)
                <div class="flex justify-between text-amber-600">
                    <span>Deposit</span>
                    <span class="font-medium">Rp {{ number_format($booking->deposit_amount, 0, ',', '.') }}</span>
                </div>
                @endif
            </div>
        </div>

        @if ($booking->status === 'pending')
        <div class="bg-white rounded-xl border border-stone-200 p-5">
            <h2 class="font-semibold text-stone-900 mb-3">Aksi</h2>
            <form method="POST" action="{{ route('portal.bookings.show', $booking) }}" onsubmit="return confirm('Yakin ingin membatalkan booking ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="w-full bg-red-50 text-red-600 border border-red-200 py-2.5 rounded-lg text-sm font-medium hover:bg-red-100 transition">
                    Batalkan Booking
                </button>
            </form>
        </div>
        @endif
    </div>
</div>
@endsection

@extends('portal.layout')
@section('content')
@php $pageTitle = 'Booking Saya'; @endphp

<div class="mb-6 flex flex-wrap items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-stone-900">Booking Saya</h1>
        <p class="text-stone-500 text-sm mt-1">Daftar seluruh booking rental Anda.</p>
    </div>
    <a href="{{ route('portal.bookings.create') }}" class="inline-flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition shadow-sm">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
        Booking Baru
    </a>
</div>

<div class="bg-white rounded-xl border border-stone-200 overflow-hidden">
    @if ($bookings->isEmpty())
        <div class="p-8 text-center text-stone-400 text-sm">
            Belum ada booking. <a href="{{ route('portal.bookings.create') }}" class="text-blue-600 font-medium hover:underline">Buat booking pertama Anda.</a>
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
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($bookings as $booking)
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
                        <tr class="border-b border-stone-50 hover:bg-stone-50 transition">
                            <td class="px-5 py-3 font-medium text-stone-900">{{ $booking->booking_number }}</td>
                            <td class="px-5 py-3 text-stone-600">{{ $booking->vehicle->name ?? '-' }}</td>
                            <td class="px-5 py-3 text-stone-500">{{ \Carbon\Carbon::parse($booking->start_date)->format('d M Y') }} - {{ \Carbon\Carbon::parse($booking->end_date)->format('d M Y') }}</td>
                            <td class="px-5 py-3">
                                <span class="inline-flex px-2.5 py-0.5 rounded-md text-xs font-semibold {{ $bkStatusColors[$booking->status] ?? 'bg-stone-100 text-stone-600' }}">
                                    {{ $bkLabels[$booking->status] ?? $booking->status }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right font-medium text-stone-900">Rp {{ number_format($booking->total_amount, 0, ',', '.') }}</td>
                            <td class="px-5 py-3 text-right">
                                <a href="{{ route('portal.bookings.show', $booking) }}" class="text-blue-600 text-sm font-medium hover:underline">Detail</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-5 py-3">{{ $bookings->links() }}</div>
    @endif
</div>
@endsection

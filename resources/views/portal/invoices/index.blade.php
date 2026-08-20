@extends('portal.layout')
@section('content')
@php $pageTitle = 'Invoice Saya'; @endphp

<div class="mb-6">
    <h1 class="text-2xl font-bold text-stone-900">Invoice Saya</h1>
    <p class="text-stone-500 text-sm mt-1">Daftar invoice dan status pembayaran Anda.</p>
</div>

<div class="bg-white rounded-xl border border-stone-200 overflow-hidden">
    @if ($invoices->isEmpty())
        <div class="p-8 text-center text-stone-400 text-sm">Belum ada invoice.</div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-stone-100 text-left text-xs font-semibold text-stone-500 uppercase tracking-wider">
                        <th class="px-5 py-3">Nomor</th>
                        <th class="px-5 py-3">Mobil</th>
                        <th class="px-5 py-3">Tanggal</th>
                        <th class="px-5 py-3 text-right">Total</th>
                        <th class="px-5 py-3 text-right">Dibayar</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($invoices as $invoice)
                        @php
                            $invStatusColors = [
                                'draft' => 'bg-stone-100 text-stone-600', 'sent' => 'bg-blue-50 text-blue-700',
                                'paid' => 'bg-emerald-50 text-emerald-700', 'partially_paid' => 'bg-amber-50 text-amber-700',
                                'overdue' => 'bg-red-50 text-red-700', 'cancelled' => 'bg-stone-100 text-stone-500',
                            ];
                            $invLabels = [
                                'draft' => 'Draft', 'sent' => 'Terkirim', 'paid' => 'Lunas',
                                'partially_paid' => 'Sebagian', 'overdue' => 'Jatuh Tempo', 'cancelled' => 'Dibatalkan',
                            ];
                        @endphp
                        <tr class="border-b border-stone-50 hover:bg-stone-50 transition">
                            <td class="px-5 py-3 font-medium text-stone-900">{{ $invoice->invoice_number }}</td>
                            <td class="px-5 py-3 text-stone-600">{{ $invoice->rentalOrder->vehicle->name ?? '-' }}</td>
                            <td class="px-5 py-3 text-stone-500">{{ $invoice->created_at->format('d M Y') }}</td>
                            <td class="px-5 py-3 text-right font-medium text-stone-900">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</td>
                            <td class="px-5 py-3 text-right text-stone-600">Rp {{ number_format($invoice->amount_paid, 0, ',', '.') }}</td>
                            <td class="px-5 py-3">
                                <span class="inline-flex px-2.5 py-0.5 rounded-md text-xs font-semibold {{ $invStatusColors[$invoice->status] ?? '' }}">
                                    {{ $invLabels[$invoice->status] ?? $invoice->status }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right space-x-3">
                                <a href="{{ route('portal.invoices.show', $invoice) }}" class="text-blue-600 text-sm font-medium hover:underline">Detail</a>
                                @if ($invoice->status !== 'paid')
                                    <a href="{{ route('portal.invoices.download', $invoice) }}" class="text-stone-500 text-sm font-medium hover:underline">PDF</a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-5 py-3">{{ $invoices->links() }}</div>
    @endif
</div>
@endsection

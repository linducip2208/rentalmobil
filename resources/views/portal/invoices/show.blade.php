@extends('portal.layout')
@section('content')
@php $pageTitle = 'Detail Invoice ' . $invoice->invoice_number; @endphp

<div class="mb-6">
    <a href="{{ route('portal.invoices.index') }}" class="text-sm text-blue-600 hover:underline inline-flex items-center gap-1 mb-2">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
        Kembali ke Invoice
    </a>
    <div class="flex flex-wrap items-center gap-3">
        <h1 class="text-2xl font-bold text-stone-900">{{ $invoice->invoice_number }}</h1>
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
        <span class="inline-flex px-2.5 py-0.5 rounded-md text-xs font-semibold {{ $invStatusColors[$invoice->status] ?? '' }}">
            {{ $invLabels[$invoice->status] ?? $invoice->status }}
        </span>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-xl border border-stone-200 p-5">
            <h2 class="font-semibold text-stone-900 mb-4">Detail Invoice</h2>
            <div class="grid grid-cols-2 gap-4 text-sm mb-4">
                <div>
                    <p class="text-stone-500">Nomor Invoice</p>
                    <p class="font-medium text-stone-900">{{ $invoice->invoice_number }}</p>
                </div>
                <div>
                    <p class="text-stone-500">Jatuh Tempo</p>
                    <p class="font-medium text-stone-900">{{ $invoice->due_date ? $invoice->due_date->format('d M Y') : '-' }}</p>
                </div>
                <div>
                    <p class="text-stone-500">Pesanan Terkait</p>
                    <p class="font-medium text-stone-900">{{ $invoice->rentalOrder->order_number ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-stone-500">Kendaraan</p>
                    <p class="font-medium text-stone-900">{{ $invoice->rentalOrder->vehicle->name ?? '-' }}</p>
                </div>
            </div>
        </div>

        @if ($invoice->payments->isNotEmpty())
        <div class="bg-white rounded-xl border border-stone-200 p-5">
            <h2 class="font-semibold text-stone-900 mb-4">Riwayat Pembayaran</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-stone-100 text-left text-xs font-semibold text-stone-500 uppercase tracking-wider">
                            <th class="pb-2">Nomor</th>
                            <th class="pb-2">Tanggal</th>
                            <th class="pb-2">Metode</th>
                            <th class="pb-2 text-right">Jumlah</th>
                            <th class="pb-2">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($invoice->payments as $payment)
                            <tr class="border-b border-stone-50">
                                <td class="py-2 font-medium text-stone-900">{{ $payment->payment_number }}</td>
                                <td class="py-2 text-stone-500">{{ $payment->payment_date->format('d M Y') }}</td>
                                <td class="py-2 text-stone-600">{{ $payment->paymentMethod->name ?? '-' }}</td>
                                <td class="py-2 text-right font-medium text-stone-900">Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                                <td class="py-2">
                                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-semibold {{ $payment->status === 'verified' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                        {{ ucfirst($payment->status) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        @if ($invoice->status !== 'paid' && $invoice->status !== 'cancelled')
        <div class="bg-white rounded-xl border border-stone-200 p-5">
            <h2 class="font-semibold text-stone-900 mb-4">Kirim Bukti Pembayaran</h2>
            <form method="POST" action="{{ route('portal.payments.store') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <input type="hidden" name="invoice_id" value="{{ $invoice->id }}">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1">Jumlah (Rp)</label>
                        <input type="number" name="amount" required min="1" value="{{ $invoice->total_amount - $invoice->amount_paid }}"
                               class="w-full px-3 py-2 rounded-lg border border-stone-300 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1">Tanggal Bayar</label>
                        <input type="date" name="payment_date" required value="{{ date('Y-m-d') }}"
                               class="w-full px-3 py-2 rounded-lg border border-stone-300 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Nomor Referensi</label>
                    <input type="text" name="reference_number" placeholder="Contoh: TRF-123456"
                           class="w-full px-3 py-2 rounded-lg border border-stone-300 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Bukti Pembayaran</label>
                    <input type="file" name="proof" required accept="image/*,.pdf"
                           class="w-full text-sm text-stone-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Catatan</label>
                    <textarea name="notes" rows="2" class="w-full px-3 py-2 rounded-lg border border-stone-300 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none"></textarea>
                </div>
                <button type="submit" class="bg-blue-600 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition">Kirim Bukti</button>
            </form>
        </div>
        @endif
    </div>

    <div class="space-y-6">
        <div class="bg-white rounded-xl border border-stone-200 p-5">
            <h2 class="font-semibold text-stone-900 mb-4">Ringkasan</h2>
            <div class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-stone-500">Subtotal</span>
                    <span class="text-stone-900">Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-stone-500">Pajak (11%)</span>
                    <span class="text-stone-900">Rp {{ number_format($invoice->tax_amount, 0, ',', '.') }}</span>
                </div>
                @if ($invoice->discount_amount > 0)
                <div class="flex justify-between text-emerald-600">
                    <span>Diskon</span>
                    <span>-Rp {{ number_format($invoice->discount_amount, 0, ',', '.') }}</span>
                </div>
                @endif
                <div class="border-t border-stone-200 pt-3 flex justify-between font-bold">
                    <span class="text-stone-900">Total</span>
                    <span class="text-stone-900">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-stone-500">Sudah Dibayar</span>
                    <span class="text-emerald-600 font-medium">Rp {{ number_format($invoice->amount_paid, 0, ',', '.') }}</span>
                </div>
                @php $balance = $invoice->total_amount - $invoice->amount_paid; @endphp
                @if ($balance > 0)
                <div class="flex justify-between">
                    <span class="text-stone-500">Sisa Tagihan</span>
                    <span class="text-red-600 font-bold">Rp {{ number_format($balance, 0, ',', '.') }}</span>
                </div>
                @endif
            </div>
        </div>

        <div class="flex flex-col gap-2">
            <a href="{{ route('portal.invoices.download', $invoice) }}" class="inline-flex items-center justify-center gap-2 bg-white border border-stone-300 text-stone-700 px-4 py-2.5 rounded-lg text-sm font-medium hover:bg-stone-50 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                Download PDF
            </a>
        </div>
    </div>
</div>
@endsection

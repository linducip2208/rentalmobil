@extends('lte.layout')

@section('title', 'Invoice '.$invoice->invoice_number)

@section('content')
<div class="row">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Invoice <span class="font-mono text-muted">{{ $invoice->invoice_number }}</span></h3></div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Pelanggan</dt><dd class="col-sm-8">{{ $invoice->customer?->name }} · {{ $invoice->customer?->email }}</dd>
                    @if($invoice->rentalOrder)<dt class="col-sm-4">Order</dt><dd class="col-sm-8">{{ $invoice->rentalOrder->order_number }} — {{ $invoice->rentalOrder->vehicle?->name }}</dd>@endif
                    <dt class="col-sm-4">Tipe</dt><dd class="col-sm-8">{{ $invoice->type }}</dd>
                    <dt class="col-sm-4">Dibuat</dt><dd class="col-sm-8">{{ $invoice->created_at?->format('d M Y') }}</dd>
                    @if($invoice->due_date)<dt class="col-sm-4">Jatuh tempo</dt><dd class="col-sm-8">{{ $invoice->due_date?->format('d M Y') }}</dd>@endif
                </dl>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3 class="card-title">Rincian</h3></div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-5">Subtotal</dt><dd class="col-sm-7 text-right">Rp {{ number_format((float) $invoice->subtotal, 0, ',', '.') }}</dd>
                    @if((float) $invoice->discount_amount > 0)<dt class="col-sm-5">Diskon</dt><dd class="col-sm-7 text-right text-success">- Rp {{ number_format((float) $invoice->discount_amount, 0, ',', '.') }}</dd>@endif
                    <dt class="col-sm-5 font-weight-bold">Total</dt><dd class="col-sm-7 text-right font-weight-bold">Rp {{ number_format((float) $invoice->total_amount, 0, ',', '.') }}</dd>
                    <dt class="col-sm-5">Dibayar</dt><dd class="col-sm-7 text-right">Rp {{ number_format((float) $invoice->total_amount - (float) $invoice->balance_due, 0, ',', '.') }}</dd>
                    <dt class="col-sm-5 font-weight-bold">Sisa</dt><dd class="col-sm-7 text-right font-weight-bold text-{{ (float) $invoice->balance_due > 0 ? 'danger' : 'success' }}">Rp {{ number_format((float) $invoice->balance_due, 0, ',', '.') }}</dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Pembayaran Masuk</h3></div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead><tr><th>Tanggal</th><th>Referensi</th><th class="text-right">Jumlah</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse($invoice->payments as $payment)
                            <tr>
                                <td>{{ $payment->payment_date?->format('d M Y') }}</td>
                                <td>{{ $payment->reference_number ?? '—' }}</td>
                                <td class="text-right">Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</td>
                                <td><span class="badge badge-{{ ['verified' => 'success', 'pending' => 'warning', 'rejected' => 'danger'][$payment->status] ?? 'secondary' }}">{{ $payment->status }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted">Belum ada pembayaran.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

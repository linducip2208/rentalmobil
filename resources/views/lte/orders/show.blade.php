@extends('lte.layout')

@section('title', 'Order '.$order->order_number)

@section('content')
<div class="row">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Detail Order <span class="font-mono text-muted">{{ $order->order_number }}</span></h3></div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Pelanggan</dt><dd class="col-sm-8">{{ $order->customer?->name }} · {{ $order->customer?->phone }}</dd>
                    <dt class="col-sm-4">Kendaraan</dt><dd class="col-sm-8">{{ $order->vehicle?->name }} ({{ $order->vehicle?->plate_number }})</dd>
                    <dt class="col-sm-4">Driver</dt><dd class="col-sm-8">{{ $order->driver?->name ?? 'Lepas kunci' }}</dd>
                    <dt class="col-sm-4">Lokasi</dt><dd class="col-sm-8">{{ $order->location?->name ?? '—' }}</dd>
                    <dt class="col-sm-4">Periode</dt><dd class="col-sm-8">{{ $order->start_date?->translatedFormat('d M Y') }} – {{ $order->end_date?->translatedFormat('d M Y') }} ({{ $order->duration_days }} hari)</dd>
                    <dt class="col-sm-4">Dibuat</dt><dd class="col-sm-8">{{ $order->created_at?->format('d M Y H:i') }}</dd>
                    @if($order->checked_out_at)<dt class="col-sm-4">Keluar</dt><dd class="col-sm-8">{{ $order->checked_out_at?->format('d M Y H:i') }}</dd>@endif
                    @if($order->checked_in_at)<dt class="col-sm-4">Kembali</dt><dd class="col-sm-8">{{ $order->checked_in_at?->format('d M Y H:i') }}</dd>@endif
                </dl>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3 class="card-title">Rincian Nilai</h3></div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-5">Tarif/hari</dt><dd class="col-sm-7 text-right">Rp {{ number_format((float) $order->daily_rate_snapshot, 0, ',', '.') }}</dd>
                    <dt class="col-sm-5">Subtotal</dt><dd class="col-sm-7 text-right">Rp {{ number_format((float) $order->subtotal, 0, ',', '.') }}</dd>
                    <dt class="col-sm-5">Add-on</dt><dd class="col-sm-7 text-right">Rp {{ number_format((float) $order->addon_total, 0, ',', '.') }}</dd>
                    <dt class="col-sm-5">Diskon</dt><dd class="col-sm-7 text-right text-success">- Rp {{ number_format((float) $order->discount_total, 0, ',', '.') }}</dd>
                    <dt class="col-sm-5">Pajak</dt><dd class="col-sm-7 text-right">Rp {{ number_format((float) $order->tax_total, 0, ',', '.') }}</dd>
                    @if((float) $order->late_fee > 0)<dt class="col-sm-5">Denda terlambat</dt><dd class="col-sm-7 text-right text-danger">Rp {{ number_format((float) $order->late_fee, 0, ',', '.') }}</dd>@endif
                    <dt class="col-sm-5 font-weight-bold">Total</dt><dd class="col-sm-7 text-right font-weight-bold">Rp {{ number_format((float) $order->final_amount, 0, ',', '.') }}</dd>
                    <dt class="col-sm-5">Dibayar</dt><dd class="col-sm-7 text-right">Rp {{ number_format((float) $order->amount_paid, 0, ',', '.') }}</dd>
                    <dt class="col-sm-5 font-weight-bold">Sisa</dt><dd class="col-sm-7 text-right font-weight-bold text-{{ (float) $order->balance_due > 0 ? 'danger' : 'success' }}">Rp {{ number_format((float) $order->balance_due, 0, ',', '.') }}</dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Deposit</h3></div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead><tr><th>Jumlah</th><th>Status</th><th>Dikembalikan</th></tr></thead>
                    <tbody>
                        @forelse($order->deposits as $deposit)
                            <tr>
                                <td>Rp {{ number_format((float) $deposit->amount, 0, ',', '.') }}</td>
                                <td><span class="badge badge-{{ ['held' => 'primary', 'received' => 'info', 'refunded' => 'success', 'forfeited' => 'danger'][$deposit->deposit_status] ?? 'secondary' }}">{{ $deposit->deposit_status }}</span></td>
                                <td>{{ $deposit->refunded_at?->format('d M Y') ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-muted">Tidak ada deposit.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3 class="card-title">Pembayaran</h3></div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead><tr><th>Tanggal</th><th class="text-right">Jumlah</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse($order->payments as $payment)
                            <tr>
                                <td>{{ $payment->payment_date?->format('d M Y') }}</td>
                                <td class="text-right">Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</td>
                                <td><span class="badge badge-{{ ['verified' => 'success', 'pending' => 'warning', 'rejected' => 'danger'][$payment->status] ?? 'secondary' }}">{{ $payment->status }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-muted">Belum ada pembayaran.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

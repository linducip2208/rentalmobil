@extends('lte.layout')

@section('title', __('Pembayaran'))

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Daftar Pembayaran ({{ $payments->total() }})</h3>
        <div class="card-tools">
            <form method="GET" class="form-inline">
                <select name="status" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
                    <option value="">Semua status</option>
                    @foreach($statuses as $key => $label)
                        <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover">
            <thead>
                <tr><th>Tanggal</th><th>Invoice</th><th>Pelanggan</th><th>Referensi</th><th class="text-right">Jumlah</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($payments as $payment)
                    <tr>
                        <td>{{ $payment->payment_date?->format('d M Y') }}</td>
                        <td>
                            @if($payment->invoice)
                                <a href="{{ route('lte.invoices.show', $payment->invoice) }}" class="font-mono">{{ $payment->invoice->invoice_number }}</a>
                                <small class="d-block text-muted">{{ $payment->invoice->customer?->name }}</small>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>{{ $payment->invoice?->customer?->name ?? '—' }}</td>
                        <td class="font-mono">{{ $payment->reference_number ?? '—' }}</td>
                        <td class="text-right">Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</td>
                        <td><span class="badge badge-{{ ['verified' => 'success', 'pending' => 'warning', 'rejected' => 'danger'][$payment->status] ?? 'secondary' }}">{{ $statuses[$payment->status] ?? $payment->status }}</span></td>
                        <td class="text-right text-nowrap">
                            @if($payment->status === 'pending')
                                <form method="POST" action="{{ route('lte.payments.verify', $payment) }}" class="d-inline" onsubmit="return confirm('Verifikasi pembayaran Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}?')">
                                    @csrf
                                    <button type="submit" class="btn btn-xs btn-success" title="Verifikasi"><i class="fas fa-check"></i></button>
                                </form>
                                <form method="POST" action="{{ route('lte.payments.reject', $payment) }}" class="d-inline" onsubmit="return confirm('Tolak pembayaran ini?')">
                                    @csrf
                                    <input type="hidden" name="reason" value="Ditolak via AdminLTE panel">
                                    <button type="submit" class="btn btn-xs btn-danger" title="Tolak"><i class="fas fa-times"></i></button>
                                </form>
                            @elseif($payment->status === 'rejected')
                                <small class="text-muted">{{ $payment->notes }}</small>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">Belum ada pembayaran.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer clearfix">{{ $payments->links() }}</div>
</div>
@endsection

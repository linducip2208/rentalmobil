@extends('lte.layout')

@section('title', __('Order Sewa'))

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Daftar Order ({{ $orders->total() }})</h3>
        <div class="card-tools">
            <form method="GET" class="form-inline">
                <select name="status" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
                    <option value="">Semua status</option>
                    @foreach($statuses as $key => $label)
                        <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
                <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm mr-2" placeholder="No. order">
                <button type="submit" class="btn btn-sm btn-outline-primary"><i class="fas fa-search"></i></button>
            </form>
        </div>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover">
            <thead>
                <tr><th>No. Order</th><th>Pelanggan</th><th>Kendaraan</th><th>Periode</th><th class="text-right">Nilai</th><th class="text-right">Sisa</th><th>Payment</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr>
                        <td class="font-mono">{{ $order->order_number }}</td>
                        <td>{{ $order->customer?->name }}</td>
                        <td>{{ $order->vehicle?->name }}</td>
                        <td>{{ $order->start_date?->format('d M') }} – {{ $order->end_date?->format('d M Y') }}</td>
                        <td class="text-right">Rp {{ number_format((float) $order->final_amount, 0, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format((float) $order->balance_due, 0, ',', '.') }}</td>
                        <td><span class="badge badge-{{ ['paid' => 'success', 'partially_paid' => 'warning', 'unpaid' => 'secondary', 'overdue' => 'danger'][$order->payment_status] ?? 'secondary' }}">{{ str_replace('_', ' ', $order->payment_status) }}</span></td>
                        <td><span class="badge badge-{{ ['completed' => 'success', 'active' => 'info', 'checked_out' => 'info', 'overdue' => 'danger', 'cancelled' => 'danger', 'disputed' => 'danger'][$order->status] ?? 'secondary' }}">{{ $statuses[$order->status] ?? $order->status }}</span></td>
                        <td class="text-right"><a href="{{ route('lte.orders.show', $order) }}" class="btn btn-xs btn-outline-primary">Detail</a></td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center text-muted py-4">Belum ada order.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer clearfix">{{ $orders->links() }}</div>
</div>
@endsection

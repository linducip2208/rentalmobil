@extends('lte.layout')

@section('title', __('Pelanggan'))

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Daftar Pelanggan ({{ $customers->total() }})</h3>
        <div class="card-tools">
            <form method="GET" class="form-inline">
                <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm mr-2" placeholder="Nama / email / telepon">
                <button type="submit" class="btn btn-sm btn-outline-primary"><i class="fas fa-search"></i></button>
            </form>
        </div>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover">
            <thead>
                <tr><th>Nama</th><th>Kontak</th><th>Tipe</th><th>Verifikasi</th><th>Order</th><th>Invoice</th><th class="text-right">Total Belanja</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($customers as $customer)
                    <tr>
                        <td>{{ $customer->name }}<small class="d-block text-muted">{{ $customer->city }}</small></td>
                        <td>{{ $customer->phone }}<small class="d-block text-muted">{{ $customer->email }}</small></td>
                        <td>{{ str_replace('_', ' ', $customer->customer_type) }}</td>
                        <td><span class="badge badge-{{ ['verified' => 'success', 'submitted' => 'warning', 'rejected' => 'danger'][$customer->verification_status] ?? 'secondary' }}">{{ $customer->verification_status }}</span></td>
                        <td>{{ $customer->rental_orders_count ?? $customer->rentalOrders()->count() }}</td>
                        <td>{{ $customer->invoices_count ?? $customer->invoices()->count() }}</td>
                        <td class="text-right">Rp {{ number_format((float) $customer->total_spent, 0, ',', '.') }}</td>
                        <td class="text-right"><a href="{{ route('lte.customers.show', $customer) }}" class="btn btn-xs btn-outline-primary">Detail</a></td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">Belum ada pelanggan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer clearfix">{{ $customers->links() }}</div>
</div>
@endsection

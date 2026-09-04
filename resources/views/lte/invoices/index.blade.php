@extends('lte.layout')

@section('title', __('Invoice'))

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Daftar Invoice ({{ $invoices->total() }})</h3>
        <div class="card-tools">
            <form method="GET" class="form-inline">
                <select name="status" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
                    <option value="">Semua status</option>
                    @foreach($statuses as $key => $label)
                        <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
                <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm mr-2" placeholder="No. invoice">
                <button type="submit" class="btn btn-sm btn-outline-primary"><i class="fas fa-search"></i></button>
            </form>
        </div>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover">
            <thead>
                <tr><th>No. Invoice</th><th>Pelanggan</th><th>Order</th><th>Dibuat</th><th class="text-right">Nilai</th><th class="text-right">Sisa</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($invoices as $invoice)
                    <tr>
                        <td class="font-mono">{{ $invoice->invoice_number }}</td>
                        <td>{{ $invoice->customer?->name }}</td>
                        <td>{{ $invoice->rental_order_id }}</td>
                        <td>{{ $invoice->created_at?->format('d M Y') }}</td>
                        <td class="text-right">Rp {{ number_format((float) $invoice->total_amount, 0, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format((float) $invoice->balance_due, 0, ',', '.') }}</td>
                        <td><span class="badge badge-{{ ['paid' => 'success', 'partially_paid' => 'warning', 'issued' => 'info', 'overdue' => 'danger', 'cancelled' => 'danger'][$invoice->status] ?? 'secondary' }}">{{ $statuses[$invoice->status] ?? $invoice->status }}</span></td>
                        <td class="text-right"><a href="{{ route('lte.invoices.show', $invoice) }}" class="btn btn-xs btn-outline-primary">Detail</a></td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">Belum ada invoice.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer clearfix">{{ $invoices->links() }}</div>
</div>
@endsection

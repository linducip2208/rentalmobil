@extends('lte.layout')

@section('title', 'Pelanggan '.$customer->name)

@section('content')
<div class="row">
    <div class="col-lg-4">
        <div class="card card-outline card-primary">
            <div class="card-body box-profile">
                <h3 class="profile-username text-center">{{ $customer->name }}</h3>
                <p class="text-muted text-center">{{ $customer->email }}</p>
                <ul class="list-group list-group-unbordered mb-3">
                    <li class="list-group-item"><b>Telepon</b> <span class="float-right">{{ $customer->phone }}</span></li>
                    <li class="list-group-item"><b>Tipe</b> <span class="float-right">{{ str_replace('_', ' ', $customer->customer_type) }}</span></li>
                    <li class="list-group-item"><b>Verifikasi</b> <span class="float-right">{{ $customer->verification_status }}</span></li>
                    <li class="list-group-item"><b>Trust score</b> <span class="float-right">{{ $customer->trust_score }}</span></li>
                    <li class="list-group-item"><b>Loyalty</b> <span class="float-right">{{ $customer->loyalty_tier }}</span></li>
                    <li class="list-group-item"><b>Total order</b> <span class="float-right">{{ $customer->rental_orders_count }}</span></li>
                    <li class="list-group-item"><b>Total belanja</b> <span class="float-right">Rp {{ number_format((float) $customer->total_spent, 0, ',', '.') }}</span></li>
                </ul>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3 class="card-title">Dokumen</h3></div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead><tr><th>Jenis</th><th>Status</th><th>Diverifikasi</th></tr></thead>
                    <tbody>
                        @forelse($documents as $document)
                            <tr>
                                <td>{{ strtoupper($document->document_type) }}@if($document->rejection_reason)<small class="d-block text-danger">{{ $document->rejection_reason }}</small>@endif</td>
                                <td><span class="badge badge-{{ ['verified' => 'success', 'pending' => 'warning', 'rejected' => 'danger'][$document->status] ?? 'secondary' }}">{{ $document->status }}</span></td>
                                <td>{{ $document->verified_at?->format('d M Y') ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-muted">Tidak ada dokumen.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Order Terakhir</h3></div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover mb-0">
                    <thead><tr><th>No. Order</th><th>Kendaraan</th><th>Periode</th><th class="text-right">Nilai</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                        @forelse($orders as $order)
                            <tr>
                                <td class="font-mono">{{ $order->order_number }}</td>
                                <td>{{ $order->vehicle?->name }}</td>
                                <td>{{ $order->start_date?->format('d M') }} – {{ $order->end_date?->format('d M Y') }}</td>
                                <td class="text-right">Rp {{ number_format((float) $order->final_amount, 0, ',', '.') }}</td>
                                <td><span class="badge badge-{{ ['completed' => 'success', 'active' => 'info', 'overdue' => 'danger', 'cancelled' => 'danger'][$order->status] ?? 'secondary' }}">{{ $order->status }}</span></td>
                                <td class="text-right"><a href="{{ route('lte.orders.show', $order) }}" class="btn btn-xs btn-outline-primary">Detail</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted">Belum ada order.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3 class="card-title">Invoice Terakhir</h3></div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover mb-0">
                    <thead><tr><th>No. Invoice</th><th class="text-right">Nilai</th><th class="text-right">Sisa</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                        @forelse($invoices as $invoice)
                            <tr>
                                <td class="font-mono">{{ $invoice->invoice_number }}</td>
                                <td class="text-right">Rp {{ number_format((float) $invoice->total_amount, 0, ',', '.') }}</td>
                                <td class="text-right">Rp {{ number_format((float) $invoice->balance_due, 0, ',', '.') }}</td>
                                <td><span class="badge badge-{{ ['paid' => 'success', 'partially_paid' => 'warning', 'issued' => 'info', 'overdue' => 'danger'][$invoice->status] ?? 'secondary' }}">{{ $invoice->status }}</span></td>
                                <td class="text-right"><a href="{{ route('lte.invoices.show', $invoice) }}" class="btn btn-xs btn-outline-primary">Detail</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted">Belum ada invoice.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

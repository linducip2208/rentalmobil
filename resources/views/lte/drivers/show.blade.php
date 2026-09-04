@extends('lte.layout')

@section('title', 'Driver '.$driver->name)

@section('content')
<div class="row">
    <div class="col-lg-4">
        <div class="card card-outline card-primary">
            <div class="card-body box-profile">
                <h3 class="profile-username text-center">{{ $driver->name }}</h3>
                <p class="text-muted text-center">{{ $driver->location?->name ?? '—' }}</p>
                <ul class="list-group list-group-unbordered mb-3">
                    <li class="list-group-item"><b>Telepon</b> <span class="float-right">{{ $driver->phone }}</span></li>
                    <li class="list-group-item"><b>Email</b> <span class="float-right">{{ $driver->email ?? '—' }}</span></li>
                    <li class="list-group-item"><b>SIM</b> <span class="float-right font-mono">{{ $driver->sim_number }} ({{ $driver->sim_type }})</span></li>
                    <li class="list-group-item"><b>SIM berlaku s.d.</b> <span class="float-right {{ $driver->hasValidSim() ? '' : 'text-danger' }}">{{ $driver->sim_expiry?->format('d M Y') ?? '—' }}</span></li>
                    <li class="list-group-item"><b>Rating</b> <span class="float-right">{{ number_format((float) $driver->rating, 2) }} / 5.00</span></li>
                    <li class="list-group-item"><b>Total trip</b> <span class="float-right">{{ $driver->total_trips }}</span></li>
                    <li class="list-group-item"><b>Status</b>
                        <span class="float-right">
                            @if(! $driver->is_active)<span class="badge badge-dark">Nonaktif</span>
                            @elseif($driver->is_available)<span class="badge badge-success">Tersedia</span>
                            @else<span class="badge badge-warning">Bertugas</span>@endif
                        </span>
                    </li>
                </ul>
                @if($driver->is_active)
                    <form method="POST" action="{{ route('lte.drivers.toggle', $driver) }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary btn-block">
                            <i class="fas fa-exchange-alt mr-1"></i>{{ $driver->is_available ? 'Tandai Sedang Bertugas' : 'Tandai Tersedia' }}
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Order Terakhir dengan Driver Ini</h3></div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover mb-0">
                    <thead><tr><th>No. Order</th><th>Kendaraan</th><th>Pelanggan</th><th>Periode</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                        @forelse($orders as $order)
                            <tr>
                                <td class="font-mono">{{ $order->order_number }}</td>
                                <td>{{ $order->vehicle?->name }}</td>
                                <td>{{ $order->customer?->name }}</td>
                                <td>{{ $order->start_date?->format('d M') }} – {{ $order->end_date?->format('d M Y') }}</td>
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
    </div>
</div>
@endsection

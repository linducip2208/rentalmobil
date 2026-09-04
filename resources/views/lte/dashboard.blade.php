@extends('lte.layout')

@section('title', __('Dashboard'))

@section('content')
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner"><h3>{{ $fleetAvailable }}<small class="text-lg">/{{ $fleetTotal }}</small></h3><p>Armada Tersedia</p></div>
            <div class="icon"><i class="fas fa-car"></i></div>
            <a href="{{ route('lte.vehicles.index', ['status' => 'available']) }}" class="small-box-footer">Lihat armada <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner"><h3>{{ $bookingsPending }}</h3><p>Booking Perlu Verifikasi</p></div>
            <div class="icon"><i class="fas fa-hourglass-half"></i></div>
            <a href="{{ route('lte.bookings.index', ['status' => 'pending_verification']) }}" class="small-box-footer">Tinjau <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner"><h3>{{ $ordersActive }}</h3><p>Order Aktif</p></div>
            <div class="icon"><i class="fas fa-file-contract"></i></div>
            <a href="{{ route('lte.orders.index', ['status' => 'active']) }}" class="small-box-footer">Lihat order <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner"><h3>Rp {{ number_format($outstanding, 0, ',', '.') }}</h3><p>Tagihan Belum Lunas</p></div>
            <div class="icon"><i class="fas fa-file-invoice-dollar"></i></div>
            <a href="{{ route('lte.invoices.index') }}" class="small-box-footer">Lihat invoice <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-4">
        <div class="info-box mb-3"><span class="info-box-icon bg-primary"><i class="fas fa-users"></i></span>
            <div class="info-box-content"><span class="info-box-text">Pelanggan aktif</span><span class="info-box-number">{{ $customersTotal }}</span></div>
        </div>
        <div class="info-box mb-3"><span class="info-box-icon bg-indigo"><i class="fas fa-calendar-check"></i></span>
            <div class="info-box-content"><span class="info-box-text">Booking terkonfirmasi</span><span class="info-box-number">{{ $bookingsActive }}</span></div>
        </div>
        <div class="info-box mb-3"><span class="info-box-icon bg-red"><i class="fas fa-exclamation-triangle"></i></span>
            <div class="info-box-content"><span class="info-box-text">Order terlambat</span><span class="info-box-number">{{ $ordersOverdue }}</span></div>
        </div>
        <div class="info-box mb-3"><span class="info-box-icon bg-teal"><i class="fas fa-money-bill-wave"></i></span>
            <div class="info-box-content"><span class="info-box-text">Pendapatan bulan ini (invoice lunas)</span><span class="info-box-number">Rp {{ number_format($revenueThisMonth, 0, ',', '.') }}</span></div>
        </div>
        <div class="info-box mb-3"><span class="info-box-icon bg-secondary"><i class="fas fa-tools"></i></span>
            <div class="info-box-content"><span class="info-box-text">Armada keluar garasi (servis/rusak)</span><span class="info-box-number">{{ $fleetOut }}</span></div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-hourglass-half mr-2"></i>Booking Terbaru</h3></div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover">
                    <thead><tr><th>No. Booking</th><th>Pelanggan</th><th>Kendaraan</th><th>Periode</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                        @forelse($recentBookings as $booking)
                            <tr>
                                <td><a href="{{ route('lte.bookings.show', $booking) }}">{{ $booking->booking_number }}</a></td>
                                <td>{{ $booking->customer?->name }}</td>
                                <td>{{ $booking->vehicle?->name }}</td>
                                <td>{{ $booking->start_date?->format('d M') }} – {{ $booking->end_date?->format('d M Y') }}</td>
                                <td><span class="badge badge-{{ ['pending_verification' => 'warning', 'pending_payment' => 'warning', 'confirmed' => 'info', 'converted' => 'success', 'cancelled' => 'danger', 'hold' => 'secondary', 'expired' => 'secondary'][$booking->status] ?? 'secondary' }}">{{ str_replace('_', ' ', $booking->status) }}</span></td>
                                <td class="text-right"><a href="{{ route('lte.bookings.show', $booking) }}" class="btn btn-xs btn-outline-primary">Detail</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">Belum ada booking.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-truck-pickup mr-2"></i>Serah Terima Mendatang</h3></div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover">
                    <thead><tr><th>No. Order</th><th>Pelanggan</th><th>Kendaraan</th><th>Ambil</th><th></th></tr></thead>
                    <tbody>
                        @forelse($upcomingPickups as $order)
                            <tr>
                                <td><a href="{{ route('lte.orders.show', $order) }}">{{ $order->order_number }}</a></td>
                                <td>{{ $order->customer?->name }}</td>
                                <td>{{ $order->vehicle?->name }}</td>
                                <td>{{ $order->start_date?->translatedFormat('d M Y') }}</td>
                                <td class="text-right"><a href="{{ route('lte.orders.show', $order) }}" class="btn btn-xs btn-outline-primary">Detail</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">Tidak ada serah terima terjadwal.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

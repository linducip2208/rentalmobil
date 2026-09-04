@extends('lte.layout')

@section('title', __('Reservasi'))

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Daftar Booking ({{ $bookings->total() }})</h3>
        <div class="card-tools">
            <form method="GET" class="form-inline">
                <select name="status" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
                    <option value="">Semua status</option>
                    @foreach($statuses as $key => $label)
                        <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
                <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm mr-2" placeholder="No. booking">
                <button type="submit" class="btn btn-sm btn-outline-primary"><i class="fas fa-search"></i></button>
            </form>
        </div>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover">
            <thead>
                <tr><th>No. Booking</th><th>Pelanggan</th><th>Kendaraan</th><th>Periode</th><th class="text-right">Total</th><th>Dep. Deposit</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($bookings as $booking)
                    <tr>
                        <td class="font-mono">{{ $booking->booking_number }}</td>
                        <td>{{ $booking->customer?->name }}<small class="d-block text-muted">{{ $booking->customer?->phone }}</small></td>
                        <td>{{ $booking->vehicle?->name }}</td>
                        <td>{{ $booking->start_date?->format('d M') }} – {{ $booking->end_date?->format('d M Y') }}</td>
                        <td class="text-right">Rp {{ number_format((float) $booking->total_amount, 0, ',', '.') }}</td>
                        <td>{{ $booking->start_date?->diffForHumans() }}</td>
                        <td><span class="badge badge-{{ ['pending_verification' => 'warning', 'pending_payment' => 'warning', 'confirmed' => 'info', 'converted' => 'success', 'cancelled' => 'danger', 'hold' => 'secondary', 'expired' => 'secondary'][$booking->status] ?? 'secondary' }}">{{ $statuses[$booking->status] ?? $booking->status }}</span></td>
                        <td class="text-right"><a href="{{ route('lte.bookings.show', $booking) }}" class="btn btn-xs btn-outline-primary">Detail</a></td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">Belum ada booking.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer clearfix">{{ $bookings->links() }}</div>
</div>
@endsection

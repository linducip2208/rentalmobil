@extends('lte.layout')

@section('title', 'Booking '.$booking->booking_number)

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Detail Booking <span class="font-mono text-muted">{{ $booking->booking_number }}</span></h3></div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Pelanggan</dt>
                    <dd class="col-sm-8">{{ $booking->customer?->name }} · {{ $booking->customer?->phone }} · {{ $booking->customer?->email }}</dd>
                    <dt class="col-sm-4">Kendaraan</dt>
                    <dd class="col-sm-8">{{ $booking->vehicle?->name }} ({{ $booking->vehicle?->plate_number }})</dd>
                    <dt class="col-sm-4">Periode</dt>
                    <dd class="col-sm-8">{{ $booking->start_date?->translatedFormat('d M Y') }} – {{ $booking->end_date?->translatedFormat('d M Y') }} ({{ $booking->duration_days }} hari)</dd>
                    <dt class="col-sm-4">Tipe sewa</dt>
                    <dd class="col-sm-8">{{ str_replace('_', ' ', $booking->rental_type) }}</dd>
                    <dt class="col-sm-4">Lokasi ambil / kembali</dt>
                    <dd class="col-sm-8">{{ $booking->pickupLocation?->name ?? '—' }} / {{ $booking->returnLocation?->name ?? '—' }}</dd>
                    <dt class="col-sm-4">Dibuat</dt>
                    <dd class="col-sm-8">{{ $booking->created_at?->format('d M Y H:i') }}</dd>
                    @if($booking->confirmed_at)<dt class="col-sm-4">Dikonfirmasi</dt><dd class="col-sm-8">{{ $booking->confirmed_at?->format('d M Y H:i') }}</dd>@endif
                    @if($booking->cancellation_reason)<dt class="col-sm-4">Alasan batal</dt><dd class="col-sm-8 text-danger">{{ $booking->cancellation_reason }}</dd>@endif
                </dl>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3 class="card-title">Rincian Harga (snapshot server)</h3></div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Tarif/hari</dt><dd class="col-sm-8 text-right">Rp {{ number_format((float) $booking->daily_rate_snapshot, 0, ',', '.') }}</dd>
                    <dt class="col-sm-4">Subtotal</dt><dd class="col-sm-8 text-right">Rp {{ number_format((float) $booking->subtotal, 0, ',', '.') }}</dd>
                    <dt class="col-sm-4">Diskon</dt><dd class="col-sm-8 text-right text-success">- Rp {{ number_format((float) $booking->discount_amount, 0, ',', '.') }}</dd>
                    <dt class="col-sm-4">Pajak</dt><dd class="col-sm-8 text-right">Rp {{ number_format((float) $booking->tax_amount, 0, ',', '.') }}</dd>
                    <dt class="col-sm-4 font-weight-bold">Total</dt><dd class="col-sm-8 text-right font-weight-bold">Rp {{ number_format((float) $booking->total_amount, 0, ',', '.') }}</dd>
                    <dt class="col-sm-4">Deposit</dt><dd class="col-sm-8 text-right">Rp {{ number_format((float) $booking->deposit_amount, 0, ',', '.') }}</dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card card-outline card-primary">
            <div class="card-header"><h3 class="card-title">Aksi</h3></div>
            <div class="card-body">
                <p>Status saat ini: <span class="badge badge-{{ ['pending_verification' => 'warning', 'pending_payment' => 'warning', 'confirmed' => 'info', 'converted' => 'success', 'cancelled' => 'danger', 'hold' => 'secondary', 'expired' => 'secondary'][$booking->status] ?? 'secondary' }}">{{ $statuses[$booking->status] ?? $booking->status }}</span></p>

                @if(in_array($booking->status, ['pending_verification', 'confirmed']))
                    <form method="POST" action="{{ route('lte.bookings.convert', $booking) }}" onsubmit="return confirm('Konversi booking ini menjadi order sewa?')">
                        @csrf
                        <button type="submit" class="btn btn-success btn-block"><i class="fas fa-arrow-right mr-1"></i>Konversi ke Order</button>
                    </form>
                @endif

                @if($booking->status === 'pending_verification')
                    <form method="POST" action="{{ route('lte.bookings.confirm', $booking) }}" class="mt-2" onsubmit="return confirm('Konfirmasi booking ini? Ketersediaan diverifikasi ulang.')">
                        @csrf
                        <button type="submit" class="btn btn-info btn-block"><i class="fas fa-check mr-1"></i>Konfirmasi Booking</button>
                    </form>
                @endif

                @if(! in_array($booking->status, ['converted', 'cancelled', 'expired']))
                    <hr>
                    <form method="POST" action="{{ route('lte.bookings.cancel', $booking) }}" onsubmit="return confirm('Batalkan booking ini?')">
                        @csrf
                        <div class="form-group">
                            <label>Alasan pembatalan <span class="text-danger">*</span></label>
                            <textarea name="reason" rows="2" class="form-control" required maxlength="500"></textarea>
                        </div>
                        <button type="submit" class="btn btn-outline-danger btn-block"><i class="fas fa-times mr-1"></i>Batalkan Booking</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@php($statuses = ['hold' => 'Ditahan Sementara', 'pending_verification' => 'Menunggu Verifikasi', 'pending_payment' => 'Menunggu Pembayaran', 'confirmed' => 'Dikonfirmasi', 'converted' => 'Menjadi Order', 'expired' => 'Kedaluwarsa', 'cancelled' => 'Dibatalkan'])

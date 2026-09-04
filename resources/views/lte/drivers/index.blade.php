@extends('lte.layout')

@section('title', __('Driver'))

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Daftar Driver ({{ $drivers->total() }})</h3>
        <div class="card-tools">
            <form method="GET" class="form-inline">
                <select name="availability" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
                    <option value="">Semua</option>
                    <option value="1" @selected(request('availability') === '1')>Tersedia</option>
                    <option value="0" @selected(request('availability') === '0')>Sedang Bertugas</option>
                </select>
                <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm mr-2" placeholder="Nama / SIM / telepon">
                <button type="submit" class="btn btn-sm btn-outline-primary"><i class="fas fa-search"></i></button>
            </form>
        </div>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover">
            <thead>
                <tr><th>Nama</th><th>Kontak</th><th>Lokasi</th><th>SIM</th><th class="text-center">Rating</th><th class="text-center">Trip</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($drivers as $driver)
                    <tr>
                        <td>{{ $driver->name }}@if(! $driver->hasValidSim())<small class="d-block text-danger">SIM kedaluwarsa {{ $driver->sim_expiry?->format('d/m/Y') }}</small>@endif</td>
                        <td>{{ $driver->phone }}</td>
                        <td>{{ $driver->location?->name ?? '—' }}</td>
                        <td class="font-mono">{{ $driver->sim_number }} <small class="text-muted">({{ $driver->sim_type }})</small></td>
                        <td class="text-center">{{ number_format((float) $driver->rating, 1) }}</td>
                        <td class="text-center">{{ $driver->total_trips }}</td>
                        <td>
                            @if(! $driver->is_active)
                                <span class="badge badge-dark">Nonaktif</span>
                            @elseif($driver->is_available)
                                <span class="badge badge-success">Tersedia</span>
                            @else
                                <span class="badge badge-warning">Bertugas</span>
                            @endif
                        </td>
                        <td class="text-right text-nowrap">
                            <a href="{{ route('lte.drivers.show', $driver) }}" class="btn btn-xs btn-outline-primary">Detail</a>
                            @if($driver->is_active)
                                <form method="POST" action="{{ route('lte.drivers.toggle', $driver) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-xs btn-outline-secondary" title="{{ $driver->is_available ? 'Tandai bertugas' : 'Tandai tersedia' }}"><i class="fas fa-exchange-alt"></i></button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">Belum ada driver.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer clearfix">{{ $drivers->links() }}</div>
</div>
@endsection

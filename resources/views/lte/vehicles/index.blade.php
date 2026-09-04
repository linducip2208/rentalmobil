@extends('lte.layout')

@section('title', __('Armada'))

@section('actions')
    <a href="{{ route('lte.vehicles.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus mr-1"></i>Tambah Kendaraan</a>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Daftar Kendaraan ({{ $vehicles->total() }})</h3>
        <div class="card-tools">
            <form method="GET" class="form-inline">
                <select name="status" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
                    <option value="">Semua status</option>
                    @foreach($statuses as $key => $label)
                        <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
                <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm mr-2" placeholder="Cari nama / polisi">
                <button type="submit" class="btn btn-sm btn-outline-primary"><i class="fas fa-search"></i></button>
            </form>
        </div>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Foto</th><th>Nama</th><th>No. Polisi</th><th>Kategori</th><th>Merek</th><th>Lokasi</th>
                    <th class="text-right">Tarif/Hari</th><th>KM</th><th>Status</th><th>Aktif</th><th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($vehicles as $vehicle)
                    <tr>
                        <td>
                            @if($vehicle->coverPhotoUrl())
                                <img src="{{ $vehicle->coverPhotoUrl() }}" alt="Foto {{ $vehicle->name }}" class="img-circle elevation-1" width="36" height="36" style="object-fit:cover" loading="lazy">
                            @else
                                <span class="img-circle bg-light d-inline-flex align-items-center justify-content-center" style="width:36px;height:36px"><i class="fas fa-car text-muted"></i></span>
                            @endif
                        </td>
                        <td>{{ $vehicle->name }}<small class="d-block text-muted">{{ $vehicle->year }} · {{ ucfirst($vehicle->transmission) }}</small></td>
                        <td class="font-mono">{{ $vehicle->plate_number }}</td>
                        <td>{{ $vehicle->category?->name }}</td>
                        <td>{{ $vehicle->brand?->name }}</td>
                        <td>{{ $vehicle->location?->name }}</td>
                        <td class="text-right">Rp {{ number_format((float) $vehicle->daily_rate, 0, ',', '.') }}</td>
                        <td>{{ number_format((int) $vehicle->mileage, 0, ',', '.') }}</td>
                        <td><span class="badge badge-{{ ['available' => 'success', 'reserved' => 'primary', 'rented' => 'info', 'preparing' => 'secondary', 'maintenance' => 'warning', 'damaged' => 'danger', 'inspection' => 'warning', 'cleaning' => 'secondary', 'overdue' => 'danger', 'inactive' => 'dark'][$vehicle->status] ?? 'secondary' }}">{{ $statuses[$vehicle->status] ?? $vehicle->status }}</span></td>
                        <td>@if($vehicle->is_active)<span class="badge badge-success">Ya</span>@else<span class="badge badge-secondary">Tidak</span>@endif</td>
                        <td class="text-right text-nowrap">
                            <a href="{{ route('lte.vehicles.edit', $vehicle) }}" class="btn btn-xs btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                            <form method="POST" action="{{ route('lte.vehicles.destroy', $vehicle) }}" class="d-inline" onsubmit="return confirm('Hapus kendaraan {{ $vehicle->name }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-xs btn-outline-danger" title="Hapus"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="11" class="text-center text-muted py-4">Belum ada kendaraan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer clearfix">{{ $vehicles->links() }}</div>
</div>
@endsection

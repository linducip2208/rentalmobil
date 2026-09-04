@extends('lte.layout')

@section('title', $vehicle->exists ? 'Edit Kendaraan' : 'Tambah Kendaraan')

@section('content')
<div class="row">
    <div class="col-lg-10">
        <div class="card">
            <div class="card-header"><h3 class="card-title">{{ $vehicle->exists ? 'Ubah data: '.$vehicle->name : 'Kendaraan baru' }}</h3></div>
            <form method="POST" enctype="multipart/form-data"
                  action="{{ $vehicle->exists ? route('lte.vehicles.update', $vehicle) : route('lte.vehicles.store') }}">
                @csrf
                @if($vehicle->exists) @method('PUT') @endif
                <div class="card-body">
                    <div class="form-group">
                        <label>Nama Kendaraan <span class="text-danger">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $vehicle->name) }}" class="form-control @error('name') is-invalid @enderror" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label>Kategori <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-control @error('category_id') is-invalid @enderror" required>
                                <option value="">Pilih</option>
                                @foreach($categories as $c)<option value="{{ $c->id }}" @selected(old('category_id', $vehicle->category_id) == $c->id)>{{ $c->name }}</option>@endforeach
                            </select>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Merek <span class="text-danger">*</span></label>
                            <select name="brand_id" class="form-control @error('brand_id') is-invalid @enderror" required>
                                <option value="">Pilih</option>
                                @foreach($brands as $b)<option value="{{ $b->id }}" @selected(old('brand_id', $vehicle->brand_id) == $b->id)>{{ $b->name }}</option>@endforeach
                            </select>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Lokasi <span class="text-danger">*</span></label>
                            <select name="location_id" class="form-control @error('location_id') is-invalid @enderror" required>
                                <option value="">Pilih</option>
                                @foreach($locations as $l)<option value="{{ $l->id }}" @selected(old('location_id', $vehicle->location_id) == $l->id)>{{ $l->name }}</option>@endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3 form-group">
                            <label>No. Polisi <span class="text-danger">*</span></label>
                            <input type="text" name="plate_number" value="{{ old('plate_number', $vehicle->plate_number) }}" class="form-control font-mono @error('plate_number') is-invalid @enderror" required>
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Tahun <span class="text-danger">*</span></label>
                            <input type="number" name="year" value="{{ old('year', $vehicle->year ?? now()->year) }}" min="1990" max="{{ now()->year + 1 }}" class="form-control" required>
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Warna</label>
                            <input type="text" name="color" value="{{ old('color', $vehicle->color) }}" class="form-control">
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Kilometer</label>
                            <input type="number" name="mileage" value="{{ old('mileage', $vehicle->mileage ?? 0) }}" min="0" class="form-control">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label>Transmisi <span class="text-danger">*</span></label>
                            <select name="transmission" class="form-control" required>
                                <option value="manual" @selected(old('transmission', $vehicle->transmission) === 'manual')>Manual</option>
                                <option value="automatic" @selected(old('transmission', $vehicle->transmission) === 'automatic')>Automatic</option>
                            </select>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Jenis BBM <span class="text-danger">*</span></label>
                            <select name="fuel_type" class="form-control" required>
                                @foreach(['pertalite' => 'Pertalite', 'pertamax' => 'Pertamax', 'premium' => 'Premium', 'diesel' => 'Diesel', 'electric' => 'Listrik'] as $k => $v)
                                    <option value="{{ $k }}" @selected(old('fuel_type', $vehicle->fuel_type) === $k)>{{ $v }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Jumlah Kursi <span class="text-danger">*</span></label>
                            <input type="number" name="seat_count" value="{{ old('seat_count', $vehicle->seat_count ?? 5) }}" min="1" max="60" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3 form-group">
                            <label>Tarif Harian (Rp) <span class="text-danger">*</span></label>
                            <input type="number" name="daily_rate" value="{{ old('daily_rate', $vehicle->daily_rate) }}" min="0" step="1000" class="form-control" required>
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Tarif Mingguan (Rp)</label>
                            <input type="number" name="weekly_rate" value="{{ old('weekly_rate', $vehicle->weekly_rate) }}" min="0" step="1000" class="form-control">
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Tarif Bulanan (Rp)</label>
                            <input type="number" name="monthly_rate" value="{{ old('monthly_rate', $vehicle->monthly_rate) }}" min="0" step="1000" class="form-control">
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Deposit (Rp)</label>
                            <input type="number" name="deposit_amount" value="{{ old('deposit_amount', $vehicle->deposit_amount) }}" min="0" step="1000" class="form-control">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label>Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-control" required>
                                @foreach(['available' => 'Tersedia', 'reserved' => 'Direservasi', 'preparing' => 'Disiapkan', 'rented' => 'Disewa', 'overdue' => 'Terlambat', 'inspection' => 'Inspeksi', 'cleaning' => 'Pembersihan', 'maintenance' => 'Servis', 'damaged' => 'Rusak', 'inactive' => 'Tidak Aktif'] as $k => $v)
                                    <option value="{{ $k }}" @selected(old('status', $vehicle->status ?? 'available') === $k)>{{ $v }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Foto Cover (JPG/PNG/WebP, maks 5MB)</label>
                            <input type="file" name="cover" accept="image/jpeg,image/png,image/webp" class="form-control-file">
                            @if($vehicle->coverPhotoUrl())<img src="{{ $vehicle->coverPhotoUrl() }}" alt="Cover sekarang" class="mt-2 rounded" width="120" loading="lazy">@endif
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="d-block mb-2">Aktif di katalog</label>
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" @checked(old('is_active', $vehicle->exists ? $vehicle->is_active : true))>
                                <label class="custom-control-label" for="is_active">Tampilkan di storefront</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('lte.vehicles.index') }}" class="btn btn-outline-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary float-right"><i class="fas fa-save mr-1"></i>Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

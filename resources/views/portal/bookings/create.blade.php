@extends('portal.layout')
@section('content')
@php $pageTitle = 'Booking Baru'; @endphp

<div class="mb-6">
    <a href="{{ route('portal.bookings.index') }}" class="text-sm text-blue-600 hover:underline inline-flex items-center gap-1 mb-2">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
        Kembali ke Booking
    </a>
    <h1 class="text-2xl font-bold text-stone-900">Booking Baru</h1>
    <p class="text-stone-500 text-sm mt-1">Isi form berikut untuk membuat booking rental mobil.</p>
</div>

<form method="POST" action="{{ route('portal.bookings.store') }}" x-data="bookingForm()" class="space-y-6">
    @csrf

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl border border-stone-200 p-5">
                <h2 class="font-semibold text-stone-900 mb-4">Pilih Kendaraan</h2>
                <select name="vehicle_id" x-model="selectedVehicleId" @change="updateVehicle()" required
                        class="w-full px-4 py-2.5 rounded-lg border border-stone-300 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none">
                    <option value="">-- Pilih Kendaraan --</option>
                    @foreach ($vehicles as $vehicle)
                        <option value="{{ $vehicle->id }}"
                                data-rate="{{ $vehicle->daily_rate }}"
                                data-deposit="{{ $vehicle->deposit_amount }}"
                                data-name="{{ $vehicle->name }}"
                                data-brand="{{ $vehicle->brand->name ?? '' }}"
                                data-category="{{ $vehicle->category->name ?? '' }}"
                                {{ old('vehicle_id') == $vehicle->id ? 'selected' : '' }}>
                            {{ $vehicle->name }} - {{ $vehicle->brand->name ?? '' }} (Rp {{ number_format($vehicle->daily_rate, 0, ',', '.') }}/hari)
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="bg-white rounded-xl border border-stone-200 p-5">
                <h2 class="font-semibold text-stone-900 mb-4">Tanggal &amp; Lokasi</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1">Tanggal Mulai</label>
                        <input type="date" name="start_date" x-model="startDate" @change="calculateCost()" min="{{ date('Y-m-d') }}" required
                               class="w-full px-3 py-2 rounded-lg border border-stone-300 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1">Tanggal Selesai</label>
                        <input type="date" name="end_date" x-model="endDate" @change="calculateCost()" :min="startDate || '{{ date('Y-m-d', strtotime('+1 day')) }}'" required
                               class="w-full px-3 py-2 rounded-lg border border-stone-300 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1">Lokasi Pengambilan</label>
                        <select name="pickup_location_id" required
                                class="w-full px-3 py-2 rounded-lg border border-stone-300 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none">
                            <option value="">-- Pilih Lokasi --</option>
                            @foreach ($locations as $location)
                                <option value="{{ $location->id }}" {{ old('pickup_location_id') == $location->id ? 'selected' : '' }}>{{ $location->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1">Lokasi Pengembalian</label>
                        <select name="return_location_id"
                                class="w-full px-3 py-2 rounded-lg border border-stone-300 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none">
                            <option value="">Sama dengan lokasi pengambilan</option>
                            @foreach ($locations as $location)
                                <option value="{{ $location->id }}" {{ old('return_location_id') == $location->id ? 'selected' : '' }}>{{ $location->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1">Waktu Pengambilan</label>
                        <input type="time" name="pickup_time" value="{{ old('pickup_time', '08:00') }}"
                               class="w-full px-3 py-2 rounded-lg border border-stone-300 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1">Waktu Pengembalian</label>
                        <input type="time" name="return_time" value="{{ old('return_time', '08:00') }}"
                               class="w-full px-3 py-2 rounded-lg border border-stone-300 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none">
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-stone-200 p-5">
                <h2 class="font-semibold text-stone-900 mb-4">Tipe Rental</h2>
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <label class="flex items-center gap-3 p-3 rounded-lg border cursor-pointer transition {{ old('rental_type', 'self_drive') === 'self_drive' ? 'border-blue-500 bg-blue-50' : 'border-stone-200 hover:bg-stone-50' }}">
                        <input type="radio" name="rental_type" value="self_drive" x-model="rentalType"
                               {{ old('rental_type', 'self_drive') === 'self_drive' ? 'checked' : '' }}
                               class="text-blue-600 focus:ring-blue-500">
                        <div>
                            <p class="text-sm font-medium text-stone-900">Lepas Kunci</p>
                            <p class="text-xs text-stone-500">Anda yang menyetir sendiri</p>
                        </div>
                    </label>
                    <label class="flex items-center gap-3 p-3 rounded-lg border cursor-pointer transition {{ old('rental_type') === 'with_driver' ? 'border-blue-500 bg-blue-50' : 'border-stone-200 hover:bg-stone-50' }}">
                        <input type="radio" name="rental_type" value="with_driver" x-model="rentalType"
                               {{ old('rental_type') === 'with_driver' ? 'checked' : '' }}
                               class="text-blue-600 focus:ring-blue-500">
                        <div>
                            <p class="text-sm font-medium text-stone-900">Dengan Driver</p>
                            <p class="text-xs text-stone-500">Kami sediakan driver</p>
                        </div>
                    </label>
                </div>
                <div x-show="rentalType === 'with_driver'" x-cloak>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Pilih Driver</label>
                    <select name="driver_id"
                            class="w-full px-3 py-2 rounded-lg border border-stone-300 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none">
                        <option value="">-- Pilih Driver --</option>
                        @foreach ($drivers as $driver)
                            <option value="{{ $driver->id }}" {{ old('driver_id') == $driver->id ? 'selected' : '' }}>
                                {{ $driver->name }} (Rating: {{ $driver->rating }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-stone-200 p-5">
                <h2 class="font-semibold text-stone-900 mb-4">Addons (Opsional)</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach ($addons as $addon)
                        <label class="flex items-start gap-3 p-3 rounded-lg border border-stone-200 hover:bg-stone-50 cursor-pointer transition">
                            <input type="checkbox" name="addons[]" value="{{ $addon->id }}"
                                   x-model="selectedAddons"
                                   data-price="{{ $addon->price }}"
                                   data-type="{{ $addon->price_type }}"
                                   class="mt-0.5 text-blue-600 focus:ring-blue-500 rounded">
                            <div>
                                <p class="text-sm font-medium text-stone-900">{{ $addon->name }}</p>
                                <p class="text-xs text-stone-500">{{ $addon->description ?? '' }}</p>
                                <p class="text-xs text-blue-600 font-medium mt-0.5">
                                    Rp {{ number_format($addon->price, 0, ',', '.') }}
                                    @if ($addon->price_type === 'daily')/hari@elseif ($addon->price_type === 'percentage')%@endif
                                </p>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="bg-white rounded-xl border border-stone-200 p-5">
                <h2 class="font-semibold text-stone-900 mb-2">Catatan</h2>
                <textarea name="notes" rows="3" placeholder="Catatan tambahan (opsional)"
                          class="w-full px-3 py-2 rounded-lg border border-stone-300 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none">{{ old('notes') }}</textarea>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-xl border border-stone-200 p-5 sticky top-20">
                <h2 class="font-semibold text-stone-900 mb-4">Ringkasan Biaya</h2>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-stone-500">Harga/Hari</span>
                        <span class="text-stone-900" x-text="'Rp ' + formatNumber(dailyRate)">Rp 0</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-stone-500">Durasi</span>
                        <span class="text-stone-900" x-text="days + ' hari'">0 hari</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-stone-500">Subtotal Sewa</span>
                        <span class="text-stone-900" x-text="'Rp ' + formatNumber(rentalSubtotal)">Rp 0</span>
                    </div>
                    <template x-if="addonTotal > 0">
                        <div class="flex justify-between">
                            <span class="text-stone-500">Addons</span>
                            <span class="text-stone-900" x-text="'Rp ' + formatNumber(addonTotal)">Rp 0</span>
                        </div>
                    </template>
                    <div class="flex justify-between">
                        <span class="text-stone-500">Pajak (11%)</span>
                        <span class="text-stone-900" x-text="'Rp ' + formatNumber(taxAmount)">Rp 0</span>
                    </div>
                    <div class="border-t border-stone-200 pt-3 flex justify-between font-bold">
                        <span class="text-stone-900">Total</span>
                        <span class="text-stone-900" x-text="'Rp ' + formatNumber(totalAmount)">Rp 0</span>
                    </div>
                    <template x-if="depositAmount > 0">
                        <div class="flex justify-between text-amber-600">
                            <span>Deposit (dikembalikan)</span>
                            <span class="font-medium" x-text="'Rp ' + formatNumber(depositAmount)">Rp 0</span>
                        </div>
                    </template>
                </div>
                <button type="submit"
                        :disabled="totalAmount <= 0"
                        class="w-full mt-6 bg-blue-600 text-white py-3 rounded-lg text-sm font-semibold hover:bg-blue-700 transition shadow-sm disabled:opacity-50 disabled:cursor-not-allowed">
                    Buat Booking
                </button>
            </div>
        </div>
    </div>
</form>

<script>
function bookingForm() {
    return {
        selectedVehicleId: '{{ old("vehicle_id") }}',
        startDate: '{{ old("start_date") }}',
        endDate: '{{ old("end_date") }}',
        rentalType: '{{ old("rental_type", "self_drive") }}',
        selectedAddons: [],
        dailyRate: 0,
        depositAmount: 0,
        days: 0,
        rentalSubtotal: 0,
        addonTotal: 0,
        taxAmount: 0,
        totalAmount: 0,

        init() {
            this.$nextTick(() => { this.updateVehicle(); this.calculateCost(); });
        },

        updateVehicle() {
            const select = document.querySelector('select[name="vehicle_id"]');
            const option = select?.querySelector('option[value="' + this.selectedVehicleId + '"]');
            if (option) {
                this.dailyRate = parseFloat(option.dataset.rate) || 0;
                this.depositAmount = parseFloat(option.dataset.deposit) || 0;
            } else {
                this.dailyRate = 0;
                this.depositAmount = 0;
            }
            this.calculateCost();
        },

        calculateCost() {
            if (this.startDate && this.endDate) {
                const start = new Date(this.startDate);
                const end = new Date(this.endDate);
                this.days = Math.max(1, Math.ceil((end - start) / (1000 * 60 * 60 * 24)));
            } else {
                this.days = 0;
            }
            this.rentalSubtotal = this.dailyRate * this.days;

            this.addonTotal = 0;
            document.querySelectorAll('input[name="addons[]"]:checked').forEach(cb => {
                const price = parseFloat(cb.dataset.price) || 0;
                const type = cb.dataset.type;
                if (type === 'daily') {
                    this.addonTotal += price * this.days;
                } else {
                    this.addonTotal += price;
                }
            });

            const subtotal = this.rentalSubtotal + this.addonTotal;
            this.taxAmount = Math.round(subtotal * 0.11);
            this.totalAmount = subtotal + this.taxAmount;
        },

        formatNumber(num) {
            return num.toLocaleString('id-ID');
        }
    };
}
</script>
@endsection

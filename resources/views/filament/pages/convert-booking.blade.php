<x-filament-panels::page>
    @php
        $drivers = $this->getDriversProperty();
    @endphp

    @if ($this->confirmed)
        {{-- Success State --}}
        <div class="flex flex-col items-center justify-center py-16">
            <div class="w-16 h-16 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center mb-4">
                <x-heroicon-o-check-circle class="w-10 h-10 text-green-600" />
            </div>
            <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100 mb-2">Konversi Berhasil!</h2>
            <p class="text-gray-500 text-sm mb-6">Booking telah berhasil dikonversi menjadi Rental Order.</p>
            <div class="flex gap-3">
                <a href="{{ \App\Filament\Resources\RentalOrderResource::getUrl('index') }}"
                   class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-semibold hover:bg-indigo-700 transition">
                    Lihat Daftar Order
                </a>
                <a href="{{ \App\Filament\Resources\BookingResource::getUrl('index') }}"
                   class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-semibold hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                    Kembali ke Booking
                </a>
            </div>
        </div>
    @elseif ($booking)
        <div class="space-y-6">
            {{-- Header --}}
            <div>
                <h2 class="text-lg font-bold">Konversi Booking ke Order Sewa</h2>
                <p class="text-sm text-gray-500">Booking #{{ $booking['booking_number'] }} — review detail dan konfirmasi konversi</p>
            </div>

            {{-- Booking Summary --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Customer & Vehicle --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm space-y-4">
                    <h3 class="font-bold text-sm uppercase tracking-wider text-gray-500">Detail Booking</h3>
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <div class="text-xs text-gray-400">Customer</div>
                            <div class="font-semibold">{{ $booking['customer_name'] }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-400">Telepon</div>
                            <div class="font-semibold">{{ $booking['customer_phone'] }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-400">Kendaraan</div>
                            <div class="font-semibold">{{ $booking['vehicle_name'] }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-400">Plat Nomor</div>
                            <div class="font-semibold">{{ $booking['vehicle_plate'] }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-400">Supir</div>
                            <div class="font-semibold">{{ $booking['driver_name'] }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-400">Status</div>
                            <span class="px-2 py-0.5 text-[10px] rounded font-semibold bg-green-100 text-green-700">{{ ucfirst($booking['status']) }}</span>
                        </div>
                    </div>
                </div>

                {{-- Schedule & Pricing --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm space-y-4">
                    <h3 class="font-bold text-sm uppercase tracking-wider text-gray-500">Jadwal & Biaya</h3>
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <div class="text-xs text-gray-400">Tanggal Mulai</div>
                            <div class="font-semibold">{{ $booking['start_date'] }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-400">Tanggal Selesai</div>
                            <div class="font-semibold">{{ $booking['end_date'] }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-400">Durasi</div>
                            <div class="font-semibold">{{ $booking['duration_days'] }} hari</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-400">Tarif/Hari</div>
                            <div class="font-semibold">Rp {{ number_format((float) $booking['daily_rate'], 0, ',', '.') }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-400">Subtotal</div>
                            <div class="font-semibold">Rp {{ number_format((float) $booking['subtotal'], 0, ',', '.') }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-400">Diskon</div>
                            <div class="font-semibold">Rp {{ number_format((float) $booking['discount_amount'], 0, ',', '.') }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-400">Pajak</div>
                            <div class="font-semibold">Rp {{ number_format((float) $booking['tax_amount'], 0, ',', '.') }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-400">Deposit</div>
                            <div class="font-semibold">Rp {{ number_format((float) $booking['deposit_amount'], 0, ',', '.') }}</div>
                        </div>
                    </div>
                    <div class="border-t pt-3 mt-3">
                        <div class="flex justify-between">
                            <span class="text-sm font-bold">Total</span>
                            <span class="text-lg font-extrabold text-indigo-600">Rp {{ number_format((float) $booking['total_amount'], 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Conversion Form --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm space-y-4">
                <h3 class="font-bold text-sm uppercase tracking-wider text-gray-500">Form Konversi</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Supir (Opsional)</label>
                        <select wire:model="driverId"
                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                            <option value="">— Tanpa supir —</option>
                            @foreach ($drivers as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Kilometer Saat Pickup</label>
                        <input type="number" wire:model="pickupKm" placeholder="Contoh: 45000"
                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500" />
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Catatan</label>
                        <textarea wire:model="notes" rows="2" placeholder="Catatan tambahan untuk order ini..."
                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500"></textarea>
                    </div>
                </div>

                {{-- Submit --}}
                <div class="flex justify-end gap-3 pt-4 border-t">
                    <a href="{{ \App\Filament\Resources\BookingResource::getUrl('index') }}"
                       class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-semibold hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                        Batal
                    </a>
                    <button wire:click="convert" wire:confirm="Yakin ingin mengkonversi booking ini ke order?"
                        class="px-6 py-2 bg-indigo-600 text-white rounded-lg text-sm font-semibold hover:bg-indigo-700 transition shadow-md hover:shadow-lg">
                        Konversi ke Order
                    </button>
                </div>
            </div>
        </div>
    @else
        <div class="py-16 text-center text-gray-400">
            <x-heroicon-o-exclamation-triangle class="w-12 h-12 mx-auto mb-3" />
            <p>Booking tidak ditemukan.</p>
        </div>
    @endif
</x-filament-panels::page>

<x-filament-panels::page>
    @if ($processed)
        {{-- Success State --}}
        <div class="flex flex-col items-center justify-center py-16">
            <div class="w-16 h-16 rounded-full {{ $action === 'approve' ? 'bg-green-100 dark:bg-green-900/30' : 'bg-red-100 dark:bg-red-900/30' }} flex items-center justify-center mb-4">
                @if ($action === 'approve')
                    <x-heroicon-o-check-circle class="w-10 h-10 text-green-600" />
                @else
                    <x-heroicon-o-x-circle class="w-10 h-10 text-red-600" />
                @endif
            </div>
            <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100 mb-2">
                {{ $action === 'approve' ? 'Pembayaran Diverifikasi!' : 'Pembayaran Ditolak' }}
            </h2>
            <p class="text-gray-500 text-sm mb-6">
                {{ $action === 'approve' ? 'Pembayaran telah berhasil diverifikasi dan invoice diperbarui.' : 'Pembayaran telah ditolak.' }}
            </p>
            <a href="{{ \App\Filament\Resources\PaymentResource::getUrl('index') }}"
               class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-semibold hover:bg-indigo-700 transition">
                Kembali ke Daftar Pembayaran
            </a>
        </div>
    @elseif ($payment)
        <div class="space-y-6">
            {{-- Header --}}
            <div>
                <h2 class="text-lg font-bold">Verifikasi Pembayaran</h2>
                <p class="text-sm text-gray-500">Payment #{{ $payment['payment_number'] }} — review bukti bayar dan putuskan</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Payment Details --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm space-y-4">
                    <h3 class="font-bold text-sm uppercase tracking-wider text-gray-500">Detail Pembayaran</h3>
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <div class="text-xs text-gray-400">Customer</div>
                            <div class="font-semibold">{{ $payment['customer_name'] }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-400">Jumlah</div>
                            <div class="text-lg font-extrabold text-indigo-600">Rp {{ number_format((float) $payment['amount'], 0, ',', '.') }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-400">Tanggal Bayar</div>
                            <div class="font-semibold">{{ $payment['payment_date'] }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-400">Metode Bayar</div>
                            <div class="font-semibold">{{ $payment['payment_method'] }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-400">No. Referensi</div>
                            <div class="font-semibold">{{ $payment['reference_number'] }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-400">Invoice</div>
                            <div class="font-semibold">{{ $payment['invoice_number'] }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-400">Order</div>
                            <div class="font-semibold">{{ $payment['order_number'] }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-400">Status Saat Ini</div>
                            @php
                                $sc = match($payment['status']) {
                                    'verified' => 'bg-green-100 text-green-700',
                                    'pending' => 'bg-yellow-100 text-yellow-700',
                                    'rejected' => 'bg-red-100 text-red-700',
                                    default => 'bg-gray-100 text-gray-600',
                                };
                            @endphp
                            <span class="px-2 py-0.5 text-[10px] rounded font-semibold {{ $sc }}">{{ ucfirst($payment['status']) }}</span>
                        </div>
                    </div>

                    @if ($payment['notes'])
                        <div class="border-t pt-3">
                            <div class="text-xs text-gray-400 mb-1">Catatan</div>
                            <div class="text-sm text-gray-600 dark:text-gray-300">{{ $payment['notes'] }}</div>
                        </div>
                    @endif
                </div>

                {{-- Proof Photo --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm space-y-4">
                    <h3 class="font-bold text-sm uppercase tracking-wider text-gray-500">Bukti Pembayaran</h3>
                    @if ($payment['proof_url'])
                        <div class="rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700">
                            <img src="{{ asset('storage/' . $payment['proof_url']) }}" alt="Bukti Pembayaran"
                                 class="w-full h-auto max-h-96 object-contain bg-gray-50 dark:bg-gray-900" />
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center py-12 text-gray-400 border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-lg">
                            <x-heroicon-o-photo class="w-10 h-10 mb-2" />
                            <p class="text-sm">Tidak ada bukti pembayaran</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Action Form --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm space-y-4">
                <h3 class="font-bold text-sm uppercase tracking-wider text-gray-500">Keputusan Verifikasi</h3>

                {{-- Action Toggle --}}
                <div class="flex gap-1 bg-gray-100 dark:bg-gray-800 rounded-lg p-1 w-fit">
                    <button wire:click="$set('action', 'approve')"
                        class="px-4 py-2 text-sm font-semibold rounded-md transition {{ $action === 'approve' ? 'bg-green-600 text-white shadow' : 'text-gray-500 hover:text-gray-700' }}">
                        ✓ Setujui
                    </button>
                    <button wire:click="$set('action', 'reject')"
                        class="px-4 py-2 text-sm font-semibold rounded-md transition {{ $action === 'reject' ? 'bg-red-600 text-white shadow' : 'text-gray-500 hover:text-gray-700' }}">
                        ✕ Tolak
                    </button>
                </div>

                {{-- Notes --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Catatan</label>
                    <textarea wire:model="notes" rows="2"
                        placeholder="{{ $action === 'approve' ? 'Catatan verifikasi (opsional)...' : 'Alasan penolakan...' }}"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>

                {{-- Submit --}}
                <div class="flex justify-end gap-3 pt-4 border-t">
                    <a href="{{ \App\Filament\Resources\PaymentResource::getUrl('index') }}"
                       class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-semibold hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                        Batal
                    </a>
                    <button wire:click="processPayment"
                        wire:confirm="{{ $action === 'approve' ? 'Yakin ingin menyetujui pembayaran ini?' : 'Yakin ingin menolak pembayaran ini?' }}"
                        class="px-6 py-2 {{ $action === 'approve' ? 'bg-green-600 hover:bg-green-700' : 'bg-red-600 hover:bg-red-700' }} text-white rounded-lg text-sm font-semibold transition shadow-md hover:shadow-lg">
                        {{ $action === 'approve' ? 'Setujui Pembayaran' : 'Tolak Pembayaran' }}
                    </button>
                </div>
            </div>
        </div>
    @else
        <div class="py-16 text-center text-gray-400">
            <x-heroicon-o-exclamation-triangle class="w-12 h-12 mx-auto mb-3" />
            <p>Pembayaran tidak ditemukan.</p>
        </div>
    @endif
</x-filament-panels::page>

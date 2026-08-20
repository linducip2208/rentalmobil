<x-filament-panels::page>
    @if ($customer)
        <div class="space-y-6">
            {{-- Header --}}
            <div>
                <h2 class="text-lg font-bold">Verifikasi Customer</h2>
                <p class="text-sm text-gray-500">{{ $customer['name'] }} — {{ $customer['email'] }}</p>
            </div>

            {{-- Customer Summary --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl p-5 shadow-sm">
                <div class="flex flex-wrap gap-6 text-sm">
                    <div>
                        <div class="text-xs text-gray-400">Tipe</div>
                        <div class="font-semibold capitalize">{{ $customer['customer_type'] }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-400">Telepon</div>
                        <div class="font-semibold">{{ $customer['phone'] }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-400">Trust Score</div>
                        <div class="font-semibold">{{ $customer['trust_score'] ?? 0 }}/100</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-400">Status Verifikasi</div>
                        @php
                            $vc = match($customer['verification_status']) {
                                'verified' => 'bg-green-100 text-green-700',
                                'pending' => 'bg-yellow-100 text-yellow-700',
                                'rejected' => 'bg-red-100 text-red-700',
                                default => 'bg-gray-100 text-gray-600',
                            };
                        @endphp
                        <span class="px-2 py-0.5 text-[10px] rounded font-semibold {{ $vc }}">{{ ucfirst($customer['verification_status'] ?? 'unverified') }}</span>
                    </div>
                </div>
            </div>

            {{-- Documents List --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm">
                <h3 class="font-bold text-sm uppercase tracking-wider text-gray-500 mb-4">Dokumen ({{ count($documents) }})</h3>

                @if (empty($documents))
                    <div class="py-8 text-center text-gray-400">
                        <x-heroicon-o-document-text class="w-10 h-10 mx-auto mb-2" />
                        <p class="text-sm">Belum ada dokumen yang diunggah</p>
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach ($documents as $doc)
                            @php
                                $docStatusColor = match($doc['status']) {
                                    'verified' => 'border-green-400 bg-green-50/50 dark:bg-green-900/10',
                                    'pending' => 'border-yellow-400 bg-yellow-50/50 dark:bg-yellow-900/10',
                                    'rejected' => 'border-red-400 bg-red-50/50 dark:bg-red-900/10',
                                    'expired' => 'border-orange-400 bg-orange-50/50 dark:bg-orange-900/10',
                                    default => 'border-gray-300 bg-gray-50/50 dark:bg-gray-800/50',
                                };
                                $docBadge = match($doc['status']) {
                                    'verified' => 'bg-green-100 text-green-700',
                                    'pending' => 'bg-yellow-100 text-yellow-700',
                                    'rejected' => 'bg-red-100 text-red-700',
                                    'expired' => 'bg-orange-100 text-orange-700',
                                    default => 'bg-gray-100 text-gray-600',
                                };
                            @endphp

                            <div class="border-l-4 {{ $docStatusColor }} rounded-lg p-4">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="font-semibold text-sm">{{ ucfirst(str_replace('_', ' ', $doc['document_type'])) }}</span>
                                            <span class="px-2 py-0.5 text-[10px] rounded font-semibold {{ $docBadge }}">{{ ucfirst($doc['status']) }}</span>
                                            @if ($doc['is_expired'])
                                                <span class="px-2 py-0.5 text-[10px] rounded font-semibold bg-red-100 text-red-700">EXPIRED</span>
                                            @endif
                                        </div>
                                        <div class="text-xs text-gray-500 space-x-4">
                                            <span>No: {{ $doc['document_number'] }}</span>
                                            @if ($doc['expiry_date'])
                                                <span>Exp: {{ $doc['expiry_date'] }}</span>
                                            @endif
                                        </div>
                                        @if ($doc['rejection_reason'])
                                            <div class="text-xs text-red-600 mt-1">Alasan Tolak: {{ $doc['rejection_reason'] }}</div>
                                        @endif
                                    </div>

                                    <div class="flex items-center gap-2">
                                        @if ($doc['document_url'])
                                            <a href="{{ asset('storage/' . $doc['document_url']) }}" target="_blank"
                                               class="px-3 py-1.5 text-xs font-semibold bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                                                Lihat
                                            </a>
                                        @endif
                                        @if ($doc['status'] !== 'verified')
                                            <button wire:click="verifyDocument({{ $doc['id'] }})"
                                                wire:confirm="Verifikasi dokumen ini?"
                                                class="px-3 py-1.5 text-xs font-semibold bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                                                Verifikasi
                                            </button>
                                        @endif
                                        @if ($doc['status'] !== 'rejected')
                                            <button wire:click="rejectDocument({{ $doc['id'] }})"
                                                wire:confirm="Tolak dokumen ini?"
                                                class="px-3 py-1.5 text-xs font-semibold bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                                                Tolak
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Back Link --}}
            <div class="flex justify-end">
                <a href="{{ \App\Filament\Resources\CustomerResource::getUrl('index') }}"
                   class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-semibold hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                    Kembali ke Daftar Customer
                </a>
            </div>
        </div>
    @else
        <div class="py-16 text-center text-gray-400">
            <x-heroicon-o-exclamation-triangle class="w-12 h-12 mx-auto mb-3" />
            <p>Customer tidak ditemukan.</p>
        </div>
    @endif
</x-filament-panels::page>

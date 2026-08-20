<x-filament-panels::page>
    @php
        $vStats = $this->getVehicleStats();
        $activity = $this->getRentalActivity();
        $maintStats = $this->getMaintenanceStats();
        $utilByCat = $this->getUtilizationByCategory();
        $maintSchedule = $this->getMaintenanceSchedule();
        $overdue = $this->getOverdueVehicles();
        $upcomingService = $this->getUpcomingService();
    @endphp

    <div class="space-y-6">
        {{-- Header + Date Filter --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="text-lg font-bold">Laporan Operasional</h2>
                <p class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($dateFrom)->translatedFormat('d M Y') }} — {{ \Carbon\Carbon::parse($dateTo)->translatedFormat('d M Y') }}</p>
            </div>
            <div class="flex gap-3 items-end flex-wrap">
                <x-filament::input.wrapper>
                    <x-filament::input type="date" wire:model.live="dateFrom" class="text-sm" />
                </x-filament::input.wrapper>
                <span class="text-gray-400">—</span>
                <x-filament::input.wrapper>
                    <x-filament::input type="date" wire:model.live="dateTo" class="text-sm" />
                </x-filament::input.wrapper>
                <x-filament::button tag="button" wire:click="$refresh" size="sm">Filter</x-filament::button>
            </div>
        </div>

        {{-- Vehicle Stats --}}
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border-l-4 border-indigo-500">
                <div class="text-xs text-gray-500 uppercase tracking-wider">Total Kendaraan</div>
                <div class="text-xl font-extrabold text-indigo-600 mt-1">{{ $vStats['total'] }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border-l-4 border-green-500">
                <div class="text-xs text-gray-500 uppercase tracking-wider">Sedang Disewa</div>
                <div class="text-xl font-extrabold text-green-600 mt-1">{{ $vStats['rented'] }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border-l-4 border-blue-500">
                <div class="text-xs text-gray-500 uppercase tracking-wider">Tersedia</div>
                <div class="text-xl font-extrabold text-blue-600 mt-1">{{ $vStats['available'] }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border-l-4 border-yellow-500">
                <div class="text-xs text-gray-500 uppercase tracking-wider">Maintenance</div>
                <div class="text-xl font-extrabold text-yellow-600 mt-1">{{ $vStats['maintenance'] }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border-l-4 border-violet-500">
                <div class="text-xs text-gray-500 uppercase tracking-wider">Utilization Rate</div>
                <div class="text-xl font-extrabold text-violet-600 mt-1">{{ $vStats['utilization_pct'] }}%</div>
            </div>
        </div>

        {{-- Rental Activity --}}
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border-l-4 border-cyan-500">
                <div class="text-xs text-gray-500 uppercase tracking-wider">Active Rentals</div>
                <div class="text-xl font-extrabold text-cyan-600 mt-1">{{ $activity['active_rentals'] }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border-l-4 border-red-500">
                <div class="text-xs text-gray-500 uppercase tracking-wider">Overdue</div>
                <div class="text-xl font-extrabold text-red-600 mt-1">{{ $activity['overdue_count'] }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border-l-4 border-emerald-500">
                <div class="text-xs text-gray-500 uppercase tracking-wider">Selesai Hari Ini</div>
                <div class="text-xl font-extrabold text-emerald-600 mt-1">{{ $activity['completed_today'] }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border-l-4 border-teal-500">
                <div class="text-xs text-gray-500 uppercase tracking-wider">Order Baru Hari Ini</div>
                <div class="text-xl font-extrabold text-teal-600 mt-1">{{ $activity['new_orders_today'] }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border-l-4 border-amber-500">
                <div class="text-xs text-gray-500 uppercase tracking-wider">Return Due Hari Ini</div>
                <div class="text-xl font-extrabold text-amber-600 mt-1">{{ $activity['return_due_today'] }}</div>
            </div>
        </div>

        {{-- Utilization by Category Chart --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm">
            <h3 class="font-bold text-sm uppercase tracking-wider text-gray-500 mb-4">Utilization per Kategori</h3>
            <div class="h-72">
                <canvas id="utilChart"></canvas>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            {{-- Maintenance Schedule --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm">
                <h3 class="font-bold text-sm uppercase tracking-wider text-gray-500 mb-4">Jadwal Maintenance</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs text-gray-500 uppercase border-b">
                                <th class="py-2">Kendaraan</th>
                                <th class="py-2">Judul</th>
                                <th class="py-2">Tipe</th>
                                <th class="py-2">Mulai</th>
                                <th class="py-2">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($maintSchedule as $log)
                                <tr class="border-b border-gray-100 dark:border-gray-700">
                                    <td class="py-2 text-xs font-medium">{{ $log['vehicle']['name'] ?? '-' }}</td>
                                    <td class="py-2 text-xs">{{ $log['title'] ?? '-' }}</td>
                                    <td class="py-2 text-xs">{{ ucfirst($log['type'] ?? '-') }}</td>
                                    <td class="py-2 text-xs">{{ $log['start_date'] ? \Carbon\Carbon::parse($log['start_date'])->format('d M Y') : '-' }}</td>
                                    <td class="py-2">
                                        @php
                                            $mc = match($log['status']) {
                                                'scheduled' => 'bg-yellow-100 text-yellow-700',
                                                'in_progress' => 'bg-blue-100 text-blue-700',
                                                default => 'bg-gray-100 text-gray-600',
                                            };
                                        @endphp
                                        <span class="px-2 py-0.5 text-[10px] rounded font-semibold {{ $mc }}">{{ ucfirst(str_replace('_', ' ', $log['status'])) }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="py-6 text-center text-gray-400">Tidak ada jadwal maintenance</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Overdue Vehicles --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm">
                <h3 class="font-bold text-sm uppercase tracking-wider text-gray-500 mb-4">Kendaraan Overdue</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs text-gray-500 uppercase border-b">
                                <th class="py-2">No. Order</th>
                                <th class="py-2">Customer</th>
                                <th class="py-2">Kendaraan</th>
                                <th class="py-2">Seharusnya Kembali</th>
                                <th class="py-2">Terlambat</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($overdue as $ord)
                                @php
                                    $daysLate = $ord['end_date'] ? \Carbon\Carbon::parse($ord['end_date'])->diffInDays(now()) : 0;
                                @endphp
                                <tr class="border-b border-gray-100 dark:border-gray-700 bg-red-50/30 dark:bg-red-900/5">
                                    <td class="py-2 text-xs font-medium">{{ $ord['order_number'] }}</td>
                                    <td class="py-2 text-xs">{{ $ord['customer']['name'] ?? '-' }}</td>
                                    <td class="py-2 text-xs">{{ $ord['vehicle']['name'] ?? '-' }}</td>
                                    <td class="py-2 text-xs text-red-600">{{ $ord['end_date'] ? \Carbon\Carbon::parse($ord['end_date'])->format('d M Y') : '-' }}</td>
                                    <td class="py-2 text-xs font-bold text-red-700">{{ $daysLate }} hari</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="py-6 text-center text-gray-400">Tidak ada kendaraan overdue</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Upcoming Service --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm">
            <h3 class="font-bold text-sm uppercase tracking-wider text-gray-500 mb-4">Service Mendatang (14 Hari)</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs text-gray-500 uppercase border-b">
                            <th class="py-2">Kendaraan</th>
                            <th class="py-2">Tipe Service</th>
                            <th class="py-2">Tanggal Service</th>
                            <th class="py-2">Estimasi Biaya</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($upcomingService as $svc)
                            @php
                                $isOverdue = $svc['next_service_date'] && \Carbon\Carbon::parse($svc['next_service_date'])->isPast();
                            @endphp
                            <tr class="border-b border-gray-100 dark:border-gray-700 {{ $isOverdue ? 'bg-yellow-50/50 dark:bg-yellow-900/10' : '' }}">
                                <td class="py-2 text-xs font-medium">{{ $svc['vehicle']['name'] ?? '-' }}</td>
                                <td class="py-2 text-xs">{{ ucfirst($svc['service_type'] ?? '-') }}</td>
                                <td class="py-2 text-xs {{ $isOverdue ? 'text-red-600 font-semibold' : '' }}">
                                    {{ $svc['next_service_date'] ? \Carbon\Carbon::parse($svc['next_service_date'])->format('d M Y') : '-' }}
                                </td>
                                <td class="py-2 text-xs">Rp {{ number_format((float) ($svc['estimated_cost'] ?? 0), 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-6 text-center text-gray-400">Tidak ada service mendatang</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('livewire:navigated', function() {
            const darkGet = (k) => document.documentElement.classList.contains('dark') ? k.dark : k.light;
            const gridColor = darkGet({light: 'rgba(0,0,0,0.06)', dark: 'rgba(255,255,255,0.06)'});

            new Chart(document.getElementById('utilChart'), {
                type: 'bar',
                data: {
                    labels: @json($utilByCat['labels']),
                    datasets: [
                        { label: 'Disewa', data: @json($utilByCat['rented']), backgroundColor: '#22c55e', borderRadius: 4 },
                        { label: 'Tersedia', data: @json($utilByCat['available']), backgroundColor: '#3b82f6', borderRadius: 4 },
                        { label: 'Maintenance', data: @json($utilByCat['maintenance']), backgroundColor: '#f59e0b', borderRadius: 4 }
                    ]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top', labels: { usePointStyle: true, padding: 16 } },
                    },
                    scales: {
                        y: { stacked: true, grid: { color: gridColor } },
                        x: { stacked: true, grid: { display: false } }
                    }
                }
            });
        });
    </script>
    @endpush
</x-filament-panels::page>

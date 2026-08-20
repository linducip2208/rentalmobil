<x-filament-panels::page>
    @php
        $summary = $this->getSummary();
        $periodData = $this->getRevenueByPeriod();
        $orders = $this->getOrders();
        $locations = $this->getLocations();
        $categories = $this->getCategories();
    @endphp

    <div class="space-y-6">
        {{-- Header + Filters --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="text-lg font-bold">Laporan Penjualan</h2>
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
                <x-filament::button tag="button" wire:click="$refresh" size="sm">
                    Filter
                </x-filament::button>
            </div>
        </div>

        {{-- Filter Tambahan --}}
        <div class="flex flex-wrap gap-3">
            <div class="w-48">
                <label class="block text-xs font-semibold text-gray-500 mb-1">Lokasi</label>
                <select wire:model.live="locationId"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                    <option value="">Semua Lokasi</option>
                    @foreach ($locations as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-48">
                <label class="block text-xs font-semibold text-gray-500 mb-1">Kategori</label>
                <select wire:model.live="categoryId"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                    <option value="">Semua Kategori</option>
                    @foreach ($categories as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Group By Toggle --}}
        <div class="flex gap-1 bg-gray-100 dark:bg-gray-800 rounded-lg p-1 w-fit">
            @foreach ($this->getFilters() as $key => $label)
                <button wire:click="$set('groupBy', '{{ $key }}')"
                    class="px-3 py-1.5 text-xs font-semibold rounded-md transition {{ $groupBy === $key ? 'bg-white dark:bg-gray-700 shadow text-indigo-600' : 'text-gray-500 hover:text-gray-700' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        {{-- Summary Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl p-5 shadow-sm border-l-4 border-indigo-500">
                <div class="text-xs text-gray-500 uppercase tracking-wider">Total Revenue</div>
                <div class="text-xl font-extrabold mt-1 text-indigo-600">Rp {{ number_format($summary['total_revenue'], 0, ',', '.') }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl p-5 shadow-sm border-l-4 border-blue-500">
                <div class="text-xs text-gray-500 uppercase tracking-wider">Total Order</div>
                <div class="text-xl font-extrabold mt-1 text-blue-600">{{ number_format($summary['total_orders']) }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl p-5 shadow-sm border-l-4 border-green-500">
                <div class="text-xs text-gray-500 uppercase tracking-wider">Avg Order Value</div>
                <div class="text-xl font-extrabold mt-1 text-green-600">Rp {{ number_format($summary['avg_order_value'], 0, ',', '.') }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl p-5 shadow-sm border-l-4 border-violet-500">
                <div class="text-xs text-gray-500 uppercase tracking-wider">Occupancy Rate</div>
                <div class="text-xl font-extrabold mt-1 text-violet-600">{{ $summary['occupancy_rate'] }}%</div>
            </div>
        </div>

        {{-- Revenue by Period Chart --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm">
            <h3 class="font-bold text-sm uppercase tracking-wider text-gray-500 mb-4">Revenue per {{ $groupBy === 'daily' ? 'Hari' : ($groupBy === 'weekly' ? 'Minggu' : 'Bulan') }}</h3>
            <div class="h-72">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

        {{-- Orders Table --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm">
            <h3 class="font-bold text-sm uppercase tracking-wider text-gray-500 mb-4">Daftar Order</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs text-gray-500 uppercase border-b">
                            <th class="py-2">No. Order</th>
                            <th class="py-2">Customer</th>
                            <th class="py-2">Kendaraan</th>
                            <th class="py-2">Mulai</th>
                            <th class="py-2">Selesai</th>
                            <th class="py-2 text-right">Total</th>
                            <th class="py-2">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($orders as $order)
                            <tr class="border-b border-gray-100 dark:border-gray-700">
                                <td class="py-2 font-medium">{{ $order['order_number'] }}</td>
                                <td class="py-2">{{ $order['customer']['name'] ?? '-' }}</td>
                                <td class="py-2">{{ $order['vehicle']['name'] ?? '-' }}</td>
                                <td class="py-2 text-xs">{{ \Carbon\Carbon::parse($order['start_date'])->format('d M Y') }}</td>
                                <td class="py-2 text-xs">{{ \Carbon\Carbon::parse($order['end_date'])->format('d M Y') }}</td>
                                <td class="py-2 text-right font-semibold">Rp {{ number_format((float) $order['final_amount'], 0, ',', '.') }}</td>
                                <td class="py-2">
                                    @php
                                        $statusColor = match($order['status']) {
                                            'draft' => 'bg-gray-100 text-gray-700',
                                            'active', 'checked_out' => 'bg-green-100 text-green-700',
                                            'completed' => 'bg-blue-100 text-blue-700',
                                            'cancelled' => 'bg-red-100 text-red-700',
                                            'overdue' => 'bg-orange-100 text-orange-700',
                                            default => 'bg-gray-100 text-gray-600',
                                        };
                                        $statusLabel = match($order['status']) {
                                            'draft' => 'Draft',
                                            'active' => 'Aktif',
                                            'checked_out' => 'Dipakai',
                                            'completed' => 'Selesai',
                                            'cancelled' => 'Dibatalkan',
                                            'overdue' => 'Terlambat',
                                            default => ucfirst($order['status']),
                                        };
                                    @endphp
                                    <span class="px-2 py-0.5 text-[10px] rounded font-semibold {{ $statusColor }}">{{ $statusLabel }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-gray-400">Belum ada data order</td>
                            </tr>
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

            new Chart(document.getElementById('revenueChart'), {
                type: 'line',
                data: {
                    labels: @json($periodData['labels']),
                    datasets: [{
                        label: 'Revenue',
                        data: @json($periodData['revenue']),
                        borderColor: '#6366f1',
                        backgroundColor: 'rgba(99, 102, 241, 0.1)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 3,
                        pointHoverRadius: 6,
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: ctx => 'Rp ' + new Intl.NumberFormat('id-ID').format(ctx.raw)
                            }
                        }
                    },
                    scales: {
                        y: {
                            ticks: { callback: v => (v/1000000).toFixed(0)+'M' },
                            grid: { color: gridColor }
                        },
                        x: { grid: { display: false } }
                    }
                }
            });
        });
    </script>
    @endpush
</x-filament-panels::page>

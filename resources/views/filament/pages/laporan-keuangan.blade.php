<x-filament-panels::page>
    @php
        $pnl = $this->getProfitLoss();
        $monthly = $this->getMonthlyPnL();
        $recentPayments = $this->getRecentPayments();
        $outstanding = $this->getOutstandingInvoices();
        $expCat = $this->getExpenseByCategory();
    @endphp

    <div class="space-y-6">
        {{-- Header + Date Filter --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="text-lg font-bold">Laporan Keuangan</h2>
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

        {{-- P&L Summary Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border-l-4 border-green-500">
                <div class="text-xs text-gray-500 uppercase tracking-wider">Total Income</div>
                <div class="text-lg font-extrabold text-green-600 mt-1">Rp {{ number_format($pnl['income'], 0, ',', '.') }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border-l-4 border-red-500">
                <div class="text-xs text-gray-500 uppercase tracking-wider">Total Expenses</div>
                <div class="text-lg font-extrabold text-red-600 mt-1">Rp {{ number_format($pnl['expenses'], 0, ',', '.') }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border-l-4 border-indigo-500">
                <div class="text-xs text-gray-500 uppercase tracking-wider">Net Profit</div>
                <div class="text-lg font-extrabold text-indigo-600 mt-1">Rp {{ number_format($pnl['net_profit'], 0, ',', '.') }}</div>
                <div class="text-xs {{ $pnl['margin_pct'] >= 0 ? 'text-green-500' : 'text-red-500' }}">{{ $pnl['margin_pct'] }}% margin</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border-l-4 border-yellow-500">
                <div class="text-xs text-gray-500 uppercase tracking-wider">Pending Payments</div>
                <div class="text-lg font-extrabold text-yellow-600 mt-1">Rp {{ number_format($pnl['pending_payments'], 0, ',', '.') }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border-l-4 border-orange-500">
                <div class="text-xs text-gray-500 uppercase tracking-wider">Outstanding AR</div>
                <div class="text-lg font-extrabold text-orange-600 mt-1">Rp {{ number_format($pnl['outstanding_invoices'], 0, ',', '.') }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border-l-4 border-violet-500">
                <div class="text-xs text-gray-500 uppercase tracking-wider">Net Cash</div>
                <div class="text-lg font-extrabold text-violet-600 mt-1">Rp {{ number_format($pnl['income'] - $pnl['expenses'], 0, ',', '.') }}</div>
            </div>
        </div>

        {{-- Monthly Income vs Expenses Chart --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm">
            <h3 class="font-bold text-sm uppercase tracking-wider text-gray-500 mb-4">Income vs Expenses Bulanan</h3>
            <div class="h-80">
                <canvas id="pnlChart"></canvas>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            {{-- Recent Payments --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm">
                <h3 class="font-bold text-sm uppercase tracking-wider text-gray-500 mb-4">Pembayaran Terbaru</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs text-gray-500 uppercase border-b">
                                <th class="py-2">No. Bayar</th>
                                <th class="py-2">Customer</th>
                                <th class="py-2 text-right">Jumlah</th>
                                <th class="py-2">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentPayments as $pay)
                                <tr class="border-b border-gray-100 dark:border-gray-700">
                                    <td class="py-2 text-xs font-medium">{{ $pay['payment_number'] }}</td>
                                    <td class="py-2 text-xs truncate max-w-32">{{ $pay['customer']['name'] ?? '-' }}</td>
                                    <td class="py-2 text-xs text-right font-semibold">Rp {{ number_format((float) $pay['amount'], 0, ',', '.') }}</td>
                                    <td class="py-2">
                                        @php
                                            $sc = match($pay['status']) {
                                                'verified' => 'bg-green-100 text-green-700',
                                                'pending' => 'bg-yellow-100 text-yellow-700',
                                                'rejected' => 'bg-red-100 text-red-700',
                                                default => 'bg-gray-100 text-gray-600',
                                            };
                                        @endphp
                                        <span class="px-2 py-0.5 text-[10px] rounded font-semibold {{ $sc }}">{{ ucfirst($pay['status']) }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="py-6 text-center text-gray-400">Belum ada pembayaran</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Outstanding Invoices --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm">
                <h3 class="font-bold text-sm uppercase tracking-wider text-gray-500 mb-4">Invoice Outstanding</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs text-gray-500 uppercase border-b">
                                <th class="py-2">No. Invoice</th>
                                <th class="py-2">Customer</th>
                                <th class="py-2 text-right">Sisa Bayar</th>
                                <th class="py-2">Jatuh Tempo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($outstanding as $inv)
                                @php
                                    $isOverdue = $inv['due_date'] && \Carbon\Carbon::parse($inv['due_date'])->isPast();
                                @endphp
                                <tr class="border-b border-gray-100 dark:border-gray-700 {{ $isOverdue ? 'bg-red-50/50 dark:bg-red-900/10' : '' }}">
                                    <td class="py-2 text-xs font-medium">{{ $inv['invoice_number'] }}</td>
                                    <td class="py-2 text-xs truncate max-w-32">{{ $inv['customer']['name'] ?? '-' }}</td>
                                    <td class="py-2 text-xs text-right font-semibold text-red-600">Rp {{ number_format((float) $inv['balance_due'], 0, ',', '.') }}</td>
                                    <td class="py-2 text-xs {{ $isOverdue ? 'text-red-600 font-semibold' : 'text-gray-500' }}">
                                        {{ $inv['due_date'] ? \Carbon\Carbon::parse($inv['due_date'])->format('d M Y') : '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="py-6 text-center text-gray-400">Tidak ada invoice outstanding</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Expense by Category --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm">
                <h3 class="font-bold text-sm uppercase tracking-wider text-gray-500 mb-4">Expense per Kategori</h3>
                <div class="h-72">
                    <canvas id="expenseCatChart"></canvas>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm">
                <h3 class="font-bold text-sm uppercase tracking-wider text-gray-500 mb-4">Detail Expense</h3>
                @if (!empty($expCat))
                    @php $totalExp = array_sum(array_column($expCat, 'total')); @endphp
                    <div class="space-y-3">
                        @foreach ($expCat as $ec)
                            @php $pct = $totalExp > 0 ? round(((float) $ec->total / $totalExp) * 100, 1) : 0; @endphp
                            <div>
                                <div class="flex justify-between text-xs mb-1">
                                    <span class="font-semibold">{{ $ec->name }}</span>
                                    <span class="text-gray-500">{{ $pct }}%</span>
                                </div>
                                <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-4 relative">
                                    <div class="bg-indigo-500 h-4 rounded-full transition-all" style="width: {{ $pct }}%"></div>
                                </div>
                                <div class="text-xs font-bold mt-0.5 text-right">Rp {{ number_format((float) $ec->total, 0, ',', '.') }}</div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="py-8 text-center text-gray-400 text-sm">Belum ada data expense</div>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('livewire:navigated', function() {
            const darkGet = (k) => document.documentElement.classList.contains('dark') ? k.dark : k.light;
            const gridColor = darkGet({light: 'rgba(0,0,0,0.06)', dark: 'rgba(255,255,255,0.06)'});

            new Chart(document.getElementById('pnlChart'), {
                type: 'bar',
                data: {
                    labels: @json($monthly['labels']),
                    datasets: [
                        { label: 'Income', data: @json($monthly['revenue']), backgroundColor: '#22c55e', borderRadius: 4 },
                        { label: 'Expenses', data: @json($monthly['expenses']), backgroundColor: '#ef4444', borderRadius: 4 },
                        { label: 'Profit', data: @json($monthly['profit']), backgroundColor: '#6366f1', borderRadius: 4, type: 'line', borderColor: '#6366f1', borderWidth: 2, fill: false, tension: 0.3 }
                    ]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top', labels: { usePointStyle: true, padding: 16 } },
                        tooltip: { callbacks: { label: ctx => 'Rp ' + new Intl.NumberFormat('id-ID').format(ctx.raw) } }
                    },
                    scales: {
                        y: { ticks: { callback: v => (v/1000000).toFixed(0)+'M' }, grid: { color: gridColor } },
                        x: { grid: { display: false } }
                    }
                }
            });

            new Chart(document.getElementById('expenseCatChart'), {
                type: 'doughnut',
                data: {
                    labels: @json(array_column($expCat, 'name')),
                    datasets: [{
                        data: @json(array_map(fn($e) => (float) $e->total, $expCat)),
                        backgroundColor: ['#ef4444','#f97316','#f59e0b','#eab308','#84cc16','#22c55e','#06b6d4','#3b82f6','#8b5cf6','#ec4899'],
                        borderColor: 'transparent', borderRadius: 4
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false, cutout: '60%',
                    plugins: {
                        legend: { position: 'bottom', labels: { padding: 12, usePointStyle: true, font: { size: 10 } } }
                    }
                }
            });
        });
    </script>
    @endpush
</x-filament-panels::page>

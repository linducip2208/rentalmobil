<x-filament-panels::page>
    @php
        $vehicles = $this->getVehiclesProperty();
        $availability = $this->getAvailabilityMapProperty();
        $days = $this->getDaysInMonthProperty();
        $current = $this->getCurrentCarbonProperty();
        $prev = $this->getPrevMonthProperty();
        $next = $this->getNextMonthProperty();
        $firstDow = \Carbon\Carbon::parse($days[0])->dayOfWeek;
        $locations = $this->getLocationsProperty();
        $categories = $this->getCategoriesProperty();
        $selectedDateBookings = $this->getSelectedDateBookingsProperty();

        $statusBg = [
            'available' => 'bg-green-100 dark:bg-green-900/50 border-green-200 dark:border-green-700',
            'booked' => 'bg-red-100 dark:bg-red-900/50 border-red-200 dark:border-red-700',
            'maintenance' => 'bg-yellow-100 dark:bg-yellow-900/50 border-yellow-200 dark:border-yellow-700',
        ];
        $statusDot = [
            'available' => 'bg-green-500',
            'booked' => 'bg-red-500',
            'maintenance' => 'bg-yellow-500',
        ];
    @endphp

    <div class="space-y-5">
        {{-- Filters --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl p-5 shadow-sm">
            <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center">
                <div class="flex-1 w-full sm:w-auto">
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Lokasi</label>
                    <select wire:model.live="locationId"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                        <option value="">Semua Lokasi</option>
                        @foreach ($locations as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex-1 w-full sm:w-auto">
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
        </div>

        {{-- Month Navigation --}}
        <div class="flex items-center justify-between bg-white dark:bg-gray-800 rounded-xl px-5 py-3 shadow-sm">
            <a href="?month={{ $prev }}{{ $locationId ? '&location_id='.$locationId : '' }}{{ $categoryId ? '&category_id='.$categoryId : '' }}"
               class="px-3 py-1.5 rounded-lg bg-indigo-50 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-200 hover:bg-indigo-100 dark:hover:bg-indigo-800 text-sm font-medium transition">
                ← {{ $current->copy()->subMonth()->translatedFormat('F Y') }}
            </a>
            <h2 class="text-lg font-bold text-gray-800 dark:text-gray-100">
                {{ $current->translatedFormat('F Y') }}
            </h2>
            <a href="?month={{ $next }}{{ $locationId ? '&location_id='.$locationId : '' }}{{ $categoryId ? '&category_id='.$categoryId : '' }}"
               class="px-3 py-1.5 rounded-lg bg-indigo-50 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-200 hover:bg-indigo-100 dark:hover:bg-indigo-800 text-sm font-medium transition">
                {{ $current->copy()->addMonth()->translatedFormat('F Y') }} →
            </a>
        </div>

        {{-- Legend --}}
        <div class="flex flex-wrap gap-3 text-xs">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-green-100 dark:bg-green-900/50 text-green-800 dark:text-green-200 font-medium">
                <span class="w-2 h-2 rounded-full bg-green-500"></span> Tersedia
            </span>
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-red-100 dark:bg-red-900/50 text-red-800 dark:text-red-200 font-medium">
                <span class="w-2 h-2 rounded-full bg-red-500"></span> Booked
            </span>
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-yellow-100 dark:bg-yellow-900/50 text-yellow-800 dark:text-yellow-200 font-medium">
                <span class="w-2 h-2 rounded-full bg-yellow-500"></span> Maintenance
            </span>
        </div>

        @if (empty($vehicles))
            <div class="bg-gray-50 dark:bg-gray-900 rounded-xl p-8 text-center text-gray-400">
                <x-heroicon-o-calendar class="w-8 h-8 mx-auto mb-2" />
                Tidak ada kendaraan ditemukan untuk filter yang dipilih.
            </div>
        @else
            {{-- Calendar Grid --}}
            @foreach ($availability as $vehicleId => $vehicleInfo)
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden">
                    {{-- Vehicle Header --}}
                    <div class="px-4 py-3 bg-gray-50 dark:bg-gray-750 border-b dark:border-gray-700 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="font-semibold text-sm">{{ $vehicleInfo['name'] }}</span>
                            <span class="text-xs text-gray-400">({{ $vehicleInfo['plate'] }})</span>
                        </div>
                        @php
                            $vsc = match($vehicleInfo['status']) {
                                'available' => 'bg-green-100 text-green-700',
                                'rented' => 'bg-red-100 text-red-700',
                                'maintenance' => 'bg-yellow-100 text-yellow-700',
                                default => 'bg-gray-100 text-gray-600',
                            };
                        @endphp
                        <span class="px-2 py-0.5 text-[10px] rounded font-semibold {{ $vsc }}">{{ ucfirst($vehicleInfo['status']) }}</span>
                    </div>

                    {{-- Days Row --}}
                    <div class="grid grid-cols-7 gap-px bg-gray-200 dark:bg-gray-700">
                        @foreach (['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'] as $label)
                            <div class="font-bold text-center text-gray-500 dark:text-gray-400 py-1.5 text-[10px] bg-white dark:bg-gray-800">{{ $label }}</div>
                        @endforeach

                        @for ($i = 0; $i < $firstDow; $i++)
                            <div class="bg-white dark:bg-gray-800"></div>
                        @endfor

                        @foreach ($days as $day)
                            @php
                                $info = $vehicleInfo['days'][$day] ?? ['status' => 'available', 'info' => null];
                                $bgClass = $statusBg[$info['status']] ?? $statusBg['available'];
                                $dotClass = $statusDot[$info['status']] ?? $statusDot['available'];
                                $dayNum = \Carbon\Carbon::parse($day)->day;
                                $isToday = $day === now()->format('Y-m-d');
                                $isSelected = $day === $selectedDate;
                            @endphp

                            <div class="relative group min-h-[48px] p-1 bg-white dark:bg-gray-800 border {{ $isToday ? 'ring-2 ring-indigo-400 dark:ring-indigo-500 z-10' : ($isSelected ? 'ring-2 ring-blue-400 z-10' : 'border-gray-100 dark:border-gray-700') }} transition-colors cursor-pointer"
                                 wire:click="$set('selectedDate', '{{ $day }}')">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-gray-700 dark:text-gray-200 text-[10px]">{{ $dayNum }}</span>
                                    <span class="w-1.5 h-1.5 rounded-full {{ $dotClass }} shrink-0"></span>
                                </div>

                                @if ($info['info'])
                                    <div class="text-[8px] mt-0.5 px-0.5 py-0.5 rounded truncate {{ $info['status'] === 'booked' ? 'bg-red-200/70 dark:bg-red-800/40 text-red-900 dark:text-red-100' : 'bg-yellow-200/70 dark:bg-yellow-800/40 text-yellow-900 dark:text-yellow-100' }}"
                                         title="{{ $info['info']['number'] ?? $info['info']['title'] ?? '' }}">
                                        {{ $info['info']['number'] ?? $info['info']['title'] ?? '' }}
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach

            {{-- Selected Date Detail --}}
            @if ($selectedDate && count($selectedDateBookings) > 0)
                <div class="bg-white dark:bg-gray-800 rounded-xl p-5 shadow-sm">
                    <h3 class="font-bold text-sm uppercase tracking-wider text-gray-500 mb-3">
                        Booking tanggal {{ \Carbon\Carbon::parse($selectedDate)->translatedFormat('d M Y') }}
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-left text-xs text-gray-500 uppercase border-b">
                                    <th class="py-2">Kendaraan</th>
                                    <th class="py-2">Customer</th>
                                    <th class="py-2">Mulai</th>
                                    <th class="py-2">Selesai</th>
                                    <th class="py-2">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($selectedDateBookings as $bkg)
                                    <tr class="border-b border-gray-100 dark:border-gray-700">
                                        <td class="py-2 text-xs font-medium">{{ $bkg['vehicle']['name'] ?? '-' }}</td>
                                        <td class="py-2 text-xs">{{ $bkg['customer']['name'] ?? '-' }}</td>
                                        <td class="py-2 text-xs">{{ $bkg['start_date'] ? \Carbon\Carbon::parse($bkg['start_date'])->format('d M Y') : '-' }}</td>
                                        <td class="py-2 text-xs">{{ $bkg['end_date'] ? \Carbon\Carbon::parse($bkg['end_date'])->format('d M Y') : '-' }}</td>
                                        <td class="py-2">
                                            @php
                                                $bsc = match($bkg['status']) {
                                                    'confirmed' => 'bg-green-100 text-green-700',
                                                    'active' => 'bg-blue-100 text-blue-700',
                                                    'pending' => 'bg-yellow-100 text-yellow-700',
                                                    default => 'bg-gray-100 text-gray-600',
                                                };
                                            @endphp
                                            <span class="px-2 py-0.5 text-[10px] rounded font-semibold {{ $bsc }}">{{ ucfirst($bkg['status']) }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        @endif
    </div>
</x-filament-panels::page>

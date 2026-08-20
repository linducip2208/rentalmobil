@php
    $actions = [
        [
            'label' => 'Booking Baru',
            'icon' => 'heroicon-o-calendar-days',
            'url' => \App\Filament\Resources\BookingResource::getUrl('create'),
            'color' => 'primary',
        ],
        [
            'label' => 'Customer Baru',
            'icon' => 'heroicon-o-user-plus',
            'url' => \App\Filament\Resources\CustomerResource::getUrl('create'),
            'color' => 'success',
        ],
        [
            'label' => 'Tambah Kendaraan',
            'icon' => 'heroicon-o-truck',
            'url' => \App\Filament\Resources\VehicleResource::getUrl('create'),
            'color' => 'info',
        ],
        [
            'label' => 'Laporan',
            'icon' => 'heroicon-o-document-chart-bar',
            'url' => '#',
            'color' => 'warning',
        ],
        [
            'label' => 'Cek Alerts',
            'icon' => 'heroicon-o-bell-alert',
            'url' => '#overdue-alerts',
            'color' => 'danger',
        ],
    ];
@endphp

<x-filament-widgets::widget>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
        @foreach ($actions as $action)
            <a
                href="{{ $action['url'] }}"
                class="flex flex-col items-center gap-2 p-4 rounded-xl bg-white dark:bg-white/5 border border-gray-200 dark:border-white/10 hover:border-{{ $action['color'] }}-400 dark:hover:border-{{ $action['color'] }}-400 transition-all duration-200 hover:shadow-lg hover:-translate-y-0.5"
            >
                <x-heroicon-o-{{ class_basename($action['icon']) }}
                    class="w-6 h-6 text-{{ $action['color'] }}-500" />
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300 text-center">
                    {{ $action['label'] }}
                </span>
            </a>
        @endforeach
    </div>
</x-filament-widgets::widget>

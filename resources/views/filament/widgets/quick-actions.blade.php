@php
    $role = auth()->user()?->role;
    $actions = collect([
        [
            'label' => 'Booking Baru',
            'icon' => 'heroicon-o-calendar-days',
            'url' => \App\Filament\Resources\BookingResource::getUrl('create'),
            'color' => 'primary',
            'roles' => ['super_admin','owner','admin','manager','cashier'],
        ],
        [
            'label' => 'Customer Baru',
            'icon' => 'heroicon-o-user-plus',
            'url' => \App\Filament\Resources\CustomerResource::getUrl('create'),
            'color' => 'success',
            'roles' => ['super_admin','owner','admin','manager','cashier'],
        ],
        [
            'label' => 'Tambah Kendaraan',
            'icon' => 'heroicon-o-truck',
            'url' => \App\Filament\Resources\VehicleResource::getUrl('create'),
            'color' => 'info',
            'roles' => ['super_admin','owner','admin','manager'],
        ],
        [
            'label' => 'Laporan',
            'icon' => 'heroicon-o-document-chart-bar',
            'url' => '/admin/laporan-penjualan',
            'color' => 'warning',
            'roles' => ['super_admin','owner','manager','finance'],
        ],
        [
            'label' => 'Cek Alerts',
            'icon' => 'heroicon-o-bell-alert',
            'url' => '/admin/operational-command-center',
            'color' => 'danger',
            'roles' => ['super_admin','owner','admin','manager','cashier'],
        ],
        ['label'=>'Peta Armada','icon'=>'heroicon-o-map','url'=>'/admin/gps-map','color'=>'info','roles'=>['super_admin','owner','admin','manager','driver']],
    ])->filter(fn($action) => in_array($role, $action['roles'], true));
@endphp

<x-filament-widgets::widget>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
        @foreach ($actions as $action)
            <a
                href="{{ $action['url'] }}"
                class="flex min-h-28 flex-col items-center justify-center gap-2 rounded-xl border border-gray-200 bg-white p-4 transition-all duration-200 hover:-translate-y-0.5 hover:border-blue-400 hover:shadow-lg dark:border-white/10 dark:bg-white/5 dark:hover:border-blue-400"
            >
                <x-dynamic-component :component="$action['icon']" class="h-6 w-6 text-blue-600" />
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300 text-center">
                    {{ $action['label'] }}
                </span>
            </a>
        @endforeach
    </div>
</x-filament-widgets::widget>

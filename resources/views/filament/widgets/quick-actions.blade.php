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
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-[0_18px_50px_-42px_rgba(15,23,42,.8)] dark:border-white/10 dark:bg-slate-900">
        <header class="flex items-end justify-between border-b border-slate-100 px-5 py-4 dark:border-white/10">
            <div><p class="font-mono text-[10px] font-bold uppercase tracking-[.18em] text-blue-600">Akses cepat</p><h3 class="mt-1 text-base font-extrabold tracking-tight text-slate-950 dark:text-white">Mulai pekerjaan</h3></div>
            <span class="hidden text-xs text-slate-400 sm:block">Aksi mengikuti hak akses Anda</span>
        </header>
        <div class="grid grid-cols-2 gap-px bg-slate-100 sm:grid-cols-3 lg:grid-cols-6 dark:bg-white/10">
        @foreach ($actions as $action)
            <a
                href="{{ $action['url'] }}"
                class="group flex min-h-28 flex-col items-start justify-between bg-white p-4 transition duration-200 hover:bg-blue-50 dark:bg-slate-900 dark:hover:bg-slate-800"
            >
                <span class="grid h-9 w-9 place-items-center rounded-lg bg-slate-100 text-blue-700 transition group-hover:bg-blue-600 group-hover:text-white dark:bg-white/10"><x-dynamic-component :component="$action['icon']" class="h-5 w-5" /></span>
                <span class="text-left text-xs font-bold text-slate-700 dark:text-slate-200">
                    {{ $action['label'] }}
                </span>
            </a>
        @endforeach
        </div>
    </section>
</x-filament-widgets::widget>

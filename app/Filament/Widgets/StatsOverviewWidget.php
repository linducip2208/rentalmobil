<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\RentalOrder;
use App\Models\Vehicle;
use App\Models\Customer;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 0;

    protected function getStats(): array
    {
        $totalRevenue = RentalOrder::where('payment_status', 'paid')->sum('total_amount');
        $activeOrders = RentalOrder::whereIn('status', ['active', 'confirmed'])->count();
        $totalVehicles = Vehicle::where('is_active', true)->count();
        $availableVehicles = Vehicle::where('status', 'available')->where('is_active', true)->count();

        return [
            Stat::make('Total Pendapatan', 'Rp ' . number_format($totalRevenue, 0, ',', '.'))
                ->description('Dari order yang sudah dibayar')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
            Stat::make('Order Aktif', $activeOrders)
                ->description('Sedang berlangsung')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('info'),
            Stat::make('Total Kendaraan', $totalVehicles)
                ->description("{$availableVehicles} tersedia")
                ->descriptionIcon('heroicon-m-truck')
                ->color('primary'),
            Stat::make('Total Customer', Customer::where('is_active', true)->count())
                ->description('Terdaftar aktif')
                ->descriptionIcon('heroicon-m-users')
                ->color('warning'),
        ];
    }
}

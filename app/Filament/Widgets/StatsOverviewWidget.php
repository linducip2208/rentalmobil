<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\RentalOrder;
use App\Models\Vehicle;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected ?string $heading = 'Ringkasan';

    protected static ?int $sort = 0;

    public static function canView(): bool
    {
        return true;
    }

    protected function getStats(): array
    {
        $totalVehicles = Vehicle::where('is_active', true)->count();
        $availableVehicles = Vehicle::where('status', 'available')->where('is_active', true)->count();
        $activeRentals = RentalOrder::whereIn('status', ['active', 'checked_out'])->count();
        $todayRevenue = Payment::where('status', 'verified')
            ->whereDate('payment_date', now()->toDateString())
            ->sum('amount');
        $pendingBookings = Booking::whereIn('status', ['inquiry', 'quoted', 'hold', 'pending_verification', 'pending_payment'])->count();

        return [
            Stat::make('Total Kendaraan', $totalVehicles)
                ->description("{$availableVehicles} tersedia")
                ->descriptionIcon('heroicon-o-truck')
                ->color('info'),
            Stat::make('Rental Aktif', $activeRentals)
                ->description('Sedang berlangsung')
                ->descriptionIcon('heroicon-o-key')
                ->color('emerald'),
            Stat::make('Pendapatan Hari Ini', 'Rp '.number_format($todayRevenue, 0, ',', '.'))
                ->description('Pembayaran terverifikasi')
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('amber'),
            Stat::make('Booking Pending', $pendingBookings)
                ->description('Menunggu konfirmasi')
                ->descriptionIcon('heroicon-o-clock')
                ->color('rose'),
        ];
    }
}

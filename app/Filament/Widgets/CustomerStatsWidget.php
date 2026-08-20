<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Customer;
use Illuminate\Support\Carbon;

class CustomerStatsWidget extends BaseWidget
{
    protected ?string $heading = 'Statistik Customer';

    protected static ?int $sort = 8;

    public static function canView(): bool
    {
        return in_array(auth()->user()?->role, ['owner', 'manager', 'sales', 'marketing']);
    }

    protected function getStats(): array
    {
        $newCustomers = Customer::where('created_at', '>=', Carbon::now()->startOfMonth())->count();

        $topCustomer = Customer::withSum('rentalOrders', 'final_amount')
            ->orderByDesc('rental_orders_sum_final_amount')
            ->first();

        $topCustomerName = $topCustomer?->name ?? '-';
        $topCustomerRevenue = $topCustomer?->rental_orders_sum_final_amount ?? 0;

        $avgTrustScore = Customer::where('is_active', true)
            ->whereNotNull('trust_score')
            ->avg('trust_score');

        return [
            Stat::make('Customer Baru Bulan Ini', $newCustomers)
                ->description('Terdaftar bulan ' . Carbon::now()->format('M Y'))
                ->descriptionIcon('heroicon-o-user-plus')
                ->color('indigo'),
            Stat::make('Top Customer', $topCustomerName)
                ->description('Rp ' . number_format($topCustomerRevenue, 0, ',', '.'))
                ->descriptionIcon('heroicon-o-trophy')
                ->color('amber'),
            Stat::make('Rata-rata Trust Score', number_format($avgTrustScore ?? 0, 1))
                ->description('Skor kepercayaan customer')
                ->descriptionIcon('heroicon-o-shield-check')
                ->color('emerald'),
        ];
    }
}

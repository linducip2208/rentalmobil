<?php

namespace App\Filament\Widgets;

use App\Models\Payment;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class RevenueChartWidget extends ChartWidget
{
    protected ?string $heading = 'Pendapatan 12 Bulan Terakhir';

    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        return in_array(auth()->user()?->role, ['owner', 'manager', 'finance']);
    }

    protected function getData(): array
    {
        $labels = [];
        $values = [];

        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $labels[] = $month->format('M Y');
            $revenue = Payment::where('status', 'verified')
                ->whereYear('payment_date', $month->year)
                ->whereMonth('payment_date', $month->month)
                ->sum('amount');
            $values[] = (float) $revenue;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Pendapatan (Rp)',
                    'data' => $values,
                    'borderColor' => '#2563eb',
                    'backgroundColor' => 'rgba(37, 99, 235, 0.10)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}

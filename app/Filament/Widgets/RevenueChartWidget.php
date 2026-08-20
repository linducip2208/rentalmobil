<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\RentalOrder;
use Illuminate\Support\Carbon;

class RevenueChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Pendapatan 7 Hari Terakhir';

    protected static ?int $sort = 1;

    protected function getData(): array
    {
        $data = collect();
        $labels = collect();
        $values = collect();

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $labels->push($date->format('d M'));
            $revenue = RentalOrder::where('payment_status', 'paid')
                ->whereDate('created_at', $date)
                ->sum('total_amount');
            $values->push((float) $revenue);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Pendapatan (Rp)',
                    'data' => $values->toArray(),
                    'backgroundColor' => '#6366f1',
                    'borderColor' => '#4f46e5',
                ],
            ],
            'labels' => $labels->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}

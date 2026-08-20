<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\RentalOrder;

class OrderStatusChartWidget extends ChartWidget
{
    protected ?string $heading = 'Status Order';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $statuses = [
            'draft' => RentalOrder::where('status', 'draft')->count(),
            'confirmed' => RentalOrder::where('status', 'confirmed')->count(),
            'active' => RentalOrder::where('status', 'active')->count(),
            'overdue' => RentalOrder::where('status', 'overdue')->count(),
            'completed' => RentalOrder::where('status', 'completed')->count(),
            'cancelled' => RentalOrder::where('status', 'cancelled')->count(),
        ];

        $colors = ['#9ca3af', '#3b82f6', '#22c55e', '#ef4444', '#6366f1', '#f59e0b'];

        return [
            'datasets' => [
                [
                    'data' => array_values($statuses),
                    'backgroundColor' => $colors,
                ],
            ],
            'labels' => ['Draft', 'Dikonfirmasi', 'Aktif', 'Terlambat', 'Selesai', 'Dibatalkan'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}

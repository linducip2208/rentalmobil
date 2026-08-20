<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\RentalOrder;

class RentalStatusChartWidget extends ChartWidget
{
    protected ?string $heading = 'Status Rental Order';

    protected static ?int $sort = 2;

    public static function canView(): bool
    {
        return in_array(auth()->user()?->role, ['owner', 'manager', 'admin_operasional']);
    }

    protected function getData(): array
    {
        $statuses = [
            'draft' => RentalOrder::where('status', 'draft')->count(),
            'confirmed' => RentalOrder::where('status', 'confirmed')->count(),
            'preparing' => RentalOrder::where('status', 'preparing')->count(),
            'ready_for_preparation' => RentalOrder::where('status', 'ready_for_preparation')->count(),
            'checked_out' => RentalOrder::where('status', 'checked_out')->count(),
            'active' => RentalOrder::where('status', 'active')->count(),
            'return_due' => RentalOrder::where('status', 'return_due')->count(),
            'overdue' => RentalOrder::where('status', 'overdue')
                ->orWhere(function ($q) {
                    $q->where('status', 'active')->where('end_date', '<', now());
                })
                ->count(),
            'completed' => RentalOrder::where('status', 'completed')->count(),
            'cancelled' => RentalOrder::where('status', 'cancelled')->count(),
        ];

        $colors = ['#9ca3af', '#3b82f6', '#8b5cf6', '#6366f1', '#06b6d4', '#22c55e', '#f59e0b', '#ef4444', '#10b981', '#f43f5e'];

        return [
            'datasets' => [
                [
                    'data' => array_values($statuses),
                    'backgroundColor' => $colors,
                ],
            ],
            'labels' => ['Draft', 'Dikonfirmasi', 'Disiapkan', 'Siap', 'Checked Out', 'Aktif', 'Jatuh Tempo', 'Terlambat', 'Selesai', 'Dibatalkan'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}

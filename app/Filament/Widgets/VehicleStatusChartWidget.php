<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Vehicle;

class VehicleStatusChartWidget extends ChartWidget
{
    protected ?string $heading = 'Status Kendaraan';

    protected static ?int $sort = 3;

    public static function canView(): bool
    {
        return in_array(auth()->user()?->role, ['owner', 'manager', 'fleet_manager']);
    }

    protected function getData(): array
    {
        $statuses = [
            'available' => Vehicle::where('status', 'available')->where('is_active', true)->count(),
            'rented' => Vehicle::where('status', 'rented')->count(),
            'reserved' => Vehicle::where('status', 'reserved')->count(),
            'maintenance' => Vehicle::where('status', 'maintenance')->count(),
            'out_of_service' => Vehicle::where('status', 'out_of_service')->count(),
        ];

        $colors = ['#22c55e', '#3b82f6', '#f59e0b', '#f97316', '#ef4444'];

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Kendaraan',
                    'data' => array_values($statuses),
                    'backgroundColor' => $colors,
                ],
            ],
            'labels' => ['Tersedia', 'Disewa', 'Direservasi', 'Maintenance', 'Out of Service'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}

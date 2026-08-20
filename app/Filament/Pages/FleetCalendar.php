<?php

namespace App\Filament\Pages;

use App\Models\RentalOrder;
use Carbon\Carbon;
use Filament\Pages\Page;

class FleetCalendar extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';
    protected static \UnitEnum|string|null $navigationGroup = '📋 Penjualan';
    protected static ?string $navigationLabel = 'Kalender Armada';
    protected static ?int $navigationSort = 24;
    protected string $view = 'filament.pages.fleet-calendar';

    public function getCalendarData(): array
    {
        $start = now()->startOfWeek();
        $days = collect(range(0, 13))->map(fn ($offset) => $start->copy()->addDays($offset));
        $orders = RentalOrder::with(['vehicle', 'customer'])
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->whereDate('start_date', '<=', $days->last())
            ->whereDate('end_date', '>=', $days->first())
            ->orderBy('start_date')->get();

        return compact('days', 'orders');
    }
}

<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Models\RentalOrder;
use Illuminate\Support\Carbon;

class OverdueAlertsWidget extends BaseWidget
{
    protected static ?string $heading = 'Peringatan Keterlambatan';

    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                RentalOrder::query()
                    ->with(['customer', 'vehicle'])
                    ->where(function ($q) {
                        $q->where('status', 'overdue')
                            ->orWhere(function ($sub) {
                                $sub->where('status', 'active')
                                    ->where('end_date', '<', Carbon::today());
                            });
                    })
                    ->orderBy('end_date', 'asc')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('No. Order')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable(),
                Tables\Columns\TextColumn::make('vehicle.name')
                    ->label('Kendaraan'),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('Jatuh Tempo')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->color('danger'),
                Tables\Columns\TextColumn::make('days_overdue')
                    ->label('Hari Terlambat')
                    ->state(fn (RentalOrder $record): int => max(0, Carbon::today()->diffInDays($record->end_date, false)))
                    ->formatStateUsing(fn (int $state): string => "{$state} hari")
                    ->color(fn (int $state): string => $state > 3 ? 'danger' : 'warning')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('late_fee')
                    ->label('Denda')
                    ->formatStateUsing(fn ($state): string => 'Rp ' . number_format((float) $state, 0, ',', '.'))
                    ->sortable(),
            ])
            ->poll('30s');
    }
}

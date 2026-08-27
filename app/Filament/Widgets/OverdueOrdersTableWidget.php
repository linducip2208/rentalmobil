<?php

namespace App\Filament\Widgets;

use App\Models\RentalOrder;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class OverdueOrdersTableWidget extends BaseWidget
{
    protected static ?string $heading = 'Order Terlambat';

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                RentalOrder::query()
                    ->where('status', 'active')
                    ->where('end_date', '<', now())
                    ->latest('end_date')
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
                    ->label('Seharusnya Kembali')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->color('danger'),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('IDR'),
                Tables\Columns\TextColumn::make('amount_paid')
                    ->label('Dibayar')
                    ->money('IDR'),
            ])
            ->poll('30s');
    }
}

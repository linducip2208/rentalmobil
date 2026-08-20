<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Models\RentalOrder;

class RecentOrdersTableWidget extends BaseWidget
{
    protected static ?string $heading = 'Order Terbaru';

    protected static ?int $sort = 4;

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
                    ->latest()
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
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Mulai')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('Selesai')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'confirmed' => 'info',
                        'preparing' => 'info',
                        'ready_for_preparation' => 'info',
                        'checked_out' => 'primary',
                        'active' => 'success',
                        'return_due' => 'warning',
                        'overdue' => 'danger',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Draft',
                        'confirmed' => 'Dikonfirmasi',
                        'preparing' => 'Disiapkan',
                        'ready_for_preparation' => 'Siap',
                        'checked_out' => 'Checked Out',
                        'active' => 'Aktif',
                        'return_due' => 'Jatuh Tempo',
                        'overdue' => 'Terlambat',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                        default => ucfirst($state),
                    }),
                Tables\Columns\TextColumn::make('final_amount')
                    ->label('Total')
                    ->formatStateUsing(fn ($state): string => 'Rp ' . number_format((float) $state, 0, ',', '.'))
                    ->sortable(),
            ])
            ->poll('30s');
    }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockMovementResource\Pages;
use App\Models\StockMovement;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class StockMovementResource extends EnterpriseResource
{
    protected static ?string $model = StockMovement::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static string|UnitEnum|null $navigationGroup = 'Procurement & Inventory';

    protected static ?string $navigationLabel = 'Mutasi Stok';

    protected static ?int $navigationSort = 7;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('occurred_at', 'desc')->columns([Tables\Columns\TextColumn::make('movement_number')->label('Nomor')->searchable(), Tables\Columns\TextColumn::make('occurred_at')->label('Waktu')->dateTime('d/m/Y H:i')->sortable(), Tables\Columns\TextColumn::make('warehouse.name')->label('Gudang'), Tables\Columns\TextColumn::make('sparePart.name')->label('Item')->searchable(), Tables\Columns\TextColumn::make('type')->badge(), Tables\Columns\TextColumn::make('quantity')->numeric(3)->color(fn ($state) => $state < 0 ? 'danger' : 'success'), Tables\Columns\TextColumn::make('unit_cost')->money('IDR'), Tables\Columns\TextColumn::make('performedBy.name')->label('Oleh')])->filters([Tables\Filters\SelectFilter::make('warehouse_id')->relationship('warehouse', 'name'), Tables\Filters\SelectFilter::make('type')->options(['opening_balance' => 'Opening', 'purchase_receipt' => 'Purchase Receipt', 'maintenance_issue' => 'Maintenance', 'transfer_out' => 'Transfer Out', 'transfer_in' => 'Transfer In', 'adjustment_in' => 'Adjustment In', 'adjustment_out' => 'Adjustment Out', 'return' => 'Return', 'correction' => 'Correction'])]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListStockMovements::route('/')];
    }
}

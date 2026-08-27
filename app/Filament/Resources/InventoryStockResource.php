<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InventoryStockResource\Pages;
use App\Models\InventoryStock;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class InventoryStockResource extends EnterpriseResource
{
    protected static ?string $model = InventoryStock::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-archive-box';

    protected static string|UnitEnum|null $navigationGroup = 'Procurement & Inventory';

    protected static ?string $navigationLabel = 'Stok Gudang';

    protected static ?int $navigationSort = 6;

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
        return $table->columns([Tables\Columns\TextColumn::make('warehouse.name')->label('Gudang')->searchable()->sortable(), Tables\Columns\TextColumn::make('sparePart.part_number')->label('No. Part')->searchable(), Tables\Columns\TextColumn::make('sparePart.name')->label('Suku Cadang')->searchable(), Tables\Columns\TextColumn::make('on_hand')->label('On Hand')->numeric(3)->sortable(), Tables\Columns\TextColumn::make('reserved')->label('Reserved')->numeric(3), Tables\Columns\TextColumn::make('available')->label('Available')->numeric(3)->color(fn ($state) => $state <= 0 ? 'danger' : null), Tables\Columns\TextColumn::make('reorder_level')->label('Reorder Point')->numeric(3), Tables\Columns\TextColumn::make('average_cost')->label('Average Cost')->money('IDR')])->filters([Tables\Filters\SelectFilter::make('warehouse_id')->label('Gudang')->relationship('warehouse', 'name'), Tables\Filters\Filter::make('low_stock')->label('Stok Rendah')->query(fn ($q) => $q->whereColumn('on_hand', '<=', 'reorder_level'))]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListInventoryStocks::route('/')];
    }
}

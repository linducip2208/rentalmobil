<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WarehouseResource\Pages;
use App\Models\Warehouse;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class WarehouseResource extends EnterpriseResource
{
    protected static ?string $model = Warehouse::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-building-office-2';

    protected static string|UnitEnum|null $navigationGroup = 'Procurement & Inventory';

    protected static ?string $navigationLabel = 'Gudang';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([Forms\Components\TextInput::make('code')->label('Kode')->required()->unique(ignoreRecord: true), Forms\Components\TextInput::make('name')->label('Nama')->required(), Forms\Components\Select::make('location_id')->label('Cabang')->relationship('location', 'name')->searchable()->preload()->required(), Forms\Components\Toggle::make('is_active')->label('Aktif')->default(true), Forms\Components\Textarea::make('address')->label('Alamat')->columnSpanFull()])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([Tables\Columns\TextColumn::make('code')->searchable(), Tables\Columns\TextColumn::make('name')->searchable()->sortable(), Tables\Columns\TextColumn::make('location.name')->label('Cabang')->sortable(), Tables\Columns\TextColumn::make('stocks_count')->counts('stocks')->label('Item'), Tables\Columns\IconColumn::make('is_active')->boolean()])->filters([Tables\Filters\SelectFilter::make('location_id')->label('Cabang')->relationship('location', 'name')])->actions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListWarehouses::route('/'), 'create' => Pages\CreateWarehouse::route('/create'), 'edit' => Pages\EditWarehouse::route('/{record}/edit')];
    }
}

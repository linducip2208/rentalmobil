<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EnterpriseResource as Resource;
use App\Filament\Resources\SparePartResource\Pages;
use App\Models\SparePart;
use BackedEnum;
use Filament\Actions;
use Filament\Forms;
use Filament\Schemas;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class SparePartResource extends Resource
{
    protected static ?string $model = SparePart::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-cube';

    protected static string|UnitEnum|null $navigationGroup = 'Procurement & Inventory';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationLabel = 'Suku Cadang';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Schemas\Components\Section::make('Informasi Spare Part')->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('part_number')
                    ->label('No. Part')
                    ->maxLength(100)
                    ->unique(ignoreRecord: true),
                Forms\Components\Select::make('category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make('unit_price')
                    ->label('Harga Satuan')
                    ->numeric()
                    ->prefix('Rp')
                    ->required(),
                Forms\Components\TextInput::make('stock')
                    ->label('Stok')
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->required(),
                Forms\Components\TextInput::make('min_stock')
                    ->label('Stok Minimum')
                    ->numeric()
                    ->default(0)
                    ->minValue(0),
                Forms\Components\TextInput::make('location_in_store')
                    ->label('Lokasi di Gudang')
                    ->maxLength(255),
                Forms\Components\TextInput::make('supplier_name')
                    ->label('Nama Supplier')
                    ->maxLength(255),
                Forms\Components\TextInput::make('supplier_phone')
                    ->label('Telp Supplier')
                    ->maxLength(20),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('part_number')
                    ->label('No. Part')
                    ->searchable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori')
                    ->sortable(),
                Tables\Columns\TextColumn::make('unit_price')
                    ->label('Harga')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock')
                    ->label('Stok')
                    ->sortable()
                    ->color(fn (int $state, SparePart $record): ?string => $record->isLowStock() ? 'danger' : null),
                Tables\Columns\TextColumn::make('min_stock')
                    ->label('Min. Stok')
                    ->sortable(),
                Tables\Columns\TextColumn::make('location_in_store')
                    ->label('Lokasi'),
                Tables\Columns\TextColumn::make('supplier_name')
                    ->label('Supplier')
                    ->searchable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_low_stock')
                    ->label('Stok Rendah')
                    ->query(fn ($query) => $query->whereColumn('stock', '<=', 'min_stock')),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSpareParts::route('/'),
            'create' => Pages\CreateSparePart::route('/create'),
            'edit' => Pages\EditSparePart::route('/{record}/edit'),
            'view' => Pages\ViewSparePart::route('/{record}'),
        ];
    }
}

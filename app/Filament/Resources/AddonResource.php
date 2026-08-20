<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AddonResource\Pages;
use App\Models\Addon;
use Filament\Forms;
use Filament\Schemas;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Actions;
use Filament\Tables\Table;
use UnitEnum;
use BackedEnum;

class AddonResource extends Resource
{
    protected static ?string $model = Addon::class;

    protected static BackedEnum | string | null $navigationIcon = 'heroicon-o-plus-circle';

    protected static string | UnitEnum | null $navigationGroup = '🗂️ Data Utama';

    protected static ?int $navigationSort = 9;

    protected static ?string $navigationLabel = 'Layanan Tambahan';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Schemas\Components\Section::make('Informasi Add-on')->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->label('Deskripsi')
                    ->rows(3)
                    ->maxLength(1000),
                Forms\Components\Select::make('type')
                    ->label('Tipe Harga')
                    ->options([
                        'fixed' => 'Harga Tetap',
                        'daily' => 'Per Hari',
                    ])
                    ->default('fixed')
                    ->required(),
                Forms\Components\TextInput::make('price')
                    ->label('Harga (Rp)')
                    ->numeric()
                    ->required()
                    ->prefix('Rp'),
                Forms\Components\TextInput::make('icon')
                    ->label('Icon')
                    ->maxLength(100),
                Forms\Components\Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
                Forms\Components\TextInput::make('sort_order')
                    ->label('Urutan')
                    ->numeric()
                    ->default(0),
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
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'fixed' => 'success',
                        'daily' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'fixed' => 'Tetap',
                        'daily' => 'Per Hari',
                        default => ucfirst($state),
                    }),
                Tables\Columns\TextColumn::make('price')
                    ->label('Harga')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('Aktif'),
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
            'index' => Pages\ListAddons::route('/'),
            'create' => Pages\CreateAddon::route('/create'),
            'edit' => Pages\EditAddon::route('/{record}/edit'),
            'view' => Pages\ViewAddon::route('/{record}'),
        ];
    }
}

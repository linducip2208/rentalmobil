<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransferResource\Pages;
use App\Models\Transfer;
use Filament\Forms;
use Filament\Schemas;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Actions;
use Filament\Tables\Table;
use UnitEnum;
use BackedEnum;

class TransferResource extends Resource
{
    protected static ?string $model = Transfer::class;

    protected static BackedEnum | string | null $navigationIcon = 'heroicon-o-arrow-path-rounded-square';

    protected static string | UnitEnum | null $navigationGroup = '🚚 Serah Terima & Logistik';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Transfer Kendaraan';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Schemas\Components\Section::make('Informasi Transfer')->schema([
                Forms\Components\Select::make('vehicle_id')
                    ->label('Kendaraan')
                    ->relationship('vehicle', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('from_location_id')
                    ->label('Dari Lokasi')
                    ->relationship('fromLocation', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('to_location_id')
                    ->label('Ke Lokasi')
                    ->relationship('toLocation', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\DatePicker::make('transfer_date')
                    ->label('Tanggal Transfer')
                    ->required(),
                Forms\Components\TextInput::make('reason')
                    ->label('Alasan')
                    ->maxLength(255),
                Forms\Components\TextInput::make('start_km')
                    ->label('KM Awal')
                    ->numeric(),
                Forms\Components\TextInput::make('end_km')
                    ->label('KM Akhir')
                    ->numeric(),
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Menunggu',
                        'in_transit' => 'Dalam Perjalanan',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                    ])
                    ->default('pending')
                    ->required(),
                Forms\Components\Textarea::make('notes')
                    ->label('Catatan')
                    ->rows(2),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('transfer_number')
                    ->label('No. Transfer')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('vehicle.name')
                    ->label('Kendaraan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('fromLocation.name')
                    ->label('Dari')
                    ->sortable(),
                Tables\Columns\TextColumn::make('toLocation.name')
                    ->label('Ke')
                    ->sortable(),
                Tables\Columns\TextColumn::make('transfer_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'in_transit' => 'info',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Menunggu',
                        'in_transit' => 'Dalam Perjalanan',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                        default => ucfirst($state),
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Menunggu',
                        'in_transit' => 'Dalam Perjalanan',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                    ]),
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
            'index' => Pages\ListTransfers::route('/'),
            'create' => Pages\CreateTransfer::route('/create'),
            'edit' => Pages\EditTransfer::route('/{record}/edit'),
            'view' => Pages\ViewTransfer::route('/{record}'),
        ];
    }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GpsLogResource\Pages;
use App\Models\GpsLog;
use Filament\Forms;
use Filament\Schemas;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;
use BackedEnum;

class GpsLogResource extends Resource
{
    protected static ?string $model = GpsLog::class;

    protected static BackedEnum | string | null $navigationIcon = 'heroicon-o-map-pin';

    protected static string | UnitEnum | null $navigationGroup = '🔧 Operasional';

    protected static ?int $navigationSort = 6;

    protected static ?string $navigationLabel = 'GPS Log';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Schemas\Components\Section::make('Data GPS')->schema([
                Forms\Components\Select::make('vehicle_id')
                    ->label('Kendaraan')
                    ->relationship('vehicle', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make('latitude')
                    ->label('Latitude')
                    ->numeric()
                    ->step(0.0000001)
                    ->required(),
                Forms\Components\TextInput::make('longitude')
                    ->label('Longitude')
                    ->numeric()
                    ->step(0.0000001)
                    ->required(),
                Forms\Components\TextInput::make('speed')
                    ->label('Kecepatan (km/h)')
                    ->numeric()
                    ->step(0.01),
                Forms\Components\TextInput::make('heading')
                    ->label('Arah (°)')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(360),
                Forms\Components\TextInput::make('accuracy')
                    ->label('Akurasi (m)')
                    ->numeric()
                    ->step(0.01),
                Forms\Components\TextInput::make('battery_level')
                    ->label('Level Baterai (%)')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100),
                Forms\Components\DateTimePicker::make('recorded_at')
                    ->label('Waktu Pencatatan')
                    ->required(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('vehicle.name')
                    ->label('Kendaraan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('latitude')
                    ->label('Lat')
                    ->sortable(),
                Tables\Columns\TextColumn::make('longitude')
                    ->label('Lng')
                    ->sortable(),
                Tables\Columns\TextColumn::make('speed')
                    ->label('Kecepatan')
                    ->suffix(' km/h')
                    ->sortable(),
                Tables\Columns\TextColumn::make('battery_level')
                    ->label('Baterai')
                    ->suffix('%')
                    ->sortable(),
                Tables\Columns\TextColumn::make('recorded_at')
                    ->label('Waktu')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('recorded_at', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGpsLogs::route('/'),
            'create' => Pages\CreateGpsLog::route('/create'),
            'edit' => Pages\EditGpsLog::route('/{record}/edit'),
            'view' => Pages\ViewGpsLog::route('/{record}'),
        ];
    }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GpsGeofenceResource\Pages;
use App\Models\GpsGeofence;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class GpsGeofenceResource extends Resource
{
    protected static ?string $model = GpsGeofence::class;
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-map';
    protected static \UnitEnum|string|null $navigationGroup = '📡 GPS & Monitoring';
    protected static ?string $navigationLabel = 'Geofence';
    protected static ?int $navigationSort = 8;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\TextInput::make('name')->label('Nama zona')->required(),
            Forms\Components\Select::make('location_id')->label('Cabang')->relationship('location', 'name')->searchable()->preload(),
            Forms\Components\Select::make('type')->label('Tipe')->options(['allowed' => 'Diizinkan', 'restricted' => 'Terlarang', 'branch' => 'Area cabang'])->required(),
            Forms\Components\KeyValue::make('geometry')->label('Geometri')->helperText('Data tersimpan di database. Untuk lingkaran isi latitude, longitude, radius_meter; untuk poligon isi points sebagai JSON.')->required()->columnSpanFull(),
            Forms\Components\Toggle::make('is_active')->label('Aktif')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')->label('Zona')->searchable(),
            Tables\Columns\TextColumn::make('location.name')->label('Cabang')->placeholder('Semua'),
            Tables\Columns\TextColumn::make('type')->label('Tipe')->badge(),
            Tables\Columns\IconColumn::make('is_active')->label('Aktif')->boolean(),
        ])->recordActions([Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListGpsGeofences::route('/'), 'create' => Pages\CreateGpsGeofence::route('/create'), 'edit' => Pages\EditGpsGeofence::route('/{record}/edit')];
    }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EnterpriseResource as Resource;
use App\Filament\Resources\GpsTrackerResource\Pages;
use App\Models\GpsTracker;
use BackedEnum;
use Filament\Actions;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class GpsTrackerResource extends Resource
{
    protected static ?string $model = GpsTracker::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-signal';

    protected static string|UnitEnum|null $navigationGroup = 'GPS & Monitoring';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Perangkat GPS';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\Section::make('Informasi Perangkat')
                ->schema([
                    Forms\Components\Select::make('vehicle_id')
                        ->label('Kendaraan')
                        ->relationship('vehicle', 'name')
                        ->searchable()
                        ->preload()
                        ->nullable(),
                    Forms\Components\Select::make('gps_integration_id')->label('Integrasi GPS')->relationship('integration', 'id')->getOptionLabelFromRecordUsing(fn ($record) => $record->provider?->name.' â€” '.$record->adapter_format)->searchable()->preload(),
                    Forms\Components\TextInput::make('external_device_id')->label('ID perangkat pada provider')->helperText('IMEI, uniqueId, deviceId, atau identifier lain sesuai provider.'),
                    Forms\Components\TextInput::make('device_id')
                        ->label('Device ID (IMEI)')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(20),
                    Forms\Components\TextInput::make('device_name')
                        ->label('Nama Perangkat')
                        ->maxLength(100),
                    Forms\Components\TextInput::make('ingest_token')
                        ->label('Token ingest perangkat')
                        ->password()->revealable()
                        ->dehydrated(fn (?string $state) => filled($state))
                        ->required(fn (string $operation) => $operation === 'create')
                        ->helperText('Salin token ke perangkat. Sistem hanya menyimpan hash dan tidak dapat menampilkannya kembali.'),
                    Forms\Components\TextInput::make('brand')
                        ->label('Merk')
                        ->helperText('Nama merek dari perangkat Anda; tidak dibatasi daftar bawaan.')
                        ->nullable(),
                    Forms\Components\TextInput::make('model')
                        ->label('Model')
                        ->maxLength(100),
                ])->columns(2),

            Forms\Components\Section::make('SIM Card')
                ->schema([
                    Forms\Components\TextInput::make('sim_card_number')
                        ->label('Nomor SIM Card')
                        ->tel()
                        ->maxLength(20),
                    Forms\Components\TextInput::make('sim_provider')
                        ->label('Provider')
                        ->placeholder('Telkomsel, XL, dll')
                        ->maxLength(50),
                ])->columns(2),

            Forms\Components\Section::make('Status')
                ->schema([
                    Forms\Components\Select::make('status')
                        ->options([
                            'active' => 'Aktif',
                            'inactive' => 'Nonaktif',
                            'maintenance' => 'Maintenance',
                            'lost' => 'Hilang',
                        ])
                        ->default('active')
                        ->required(),
                    Forms\Components\Toggle::make('is_active')
                        ->default(true),
                    Forms\Components\DatePicker::make('installed_at')
                        ->label('Tanggal Instalasi'),
                    Forms\Components\Textarea::make('notes')
                        ->label('Catatan')
                        ->rows(3),
                ])->columns(2),

            Forms\Components\Section::make('Batas & Geofence')
                ->schema([
                    Forms\Components\TextInput::make('speed_limit_kmh')->label('Batas kecepatan (km/jam)')->numeric()->minValue(1),
                    Forms\Components\TextInput::make('geofence_latitude')->label('Latitude pusat')->numeric(),
                    Forms\Components\TextInput::make('geofence_longitude')->label('Longitude pusat')->numeric(),
                    Forms\Components\TextInput::make('geofence_radius_m')->label('Radius geofence (meter)')->numeric()->minValue(50),
                ])->columns(2),

            Forms\Components\Section::make('Lokasi Terakhir')
                ->schema([
                    Forms\Components\TextInput::make('last_latitude')
                        ->label('Latitude')
                        ->numeric()
                        ->readOnly(),
                    Forms\Components\TextInput::make('last_longitude')
                        ->label('Longitude')
                        ->numeric()
                        ->readOnly(),
                    Forms\Components\TextInput::make('last_speed')
                        ->label('Speed (km/h)')
                        ->numeric()
                        ->readOnly(),
                    Forms\Components\TextInput::make('last_battery_level')
                        ->label('Battery (%)')
                        ->numeric()
                        ->readOnly(),
                    Forms\Components\DateTimePicker::make('last_update_at')
                        ->label('Terakhir Update')
                        ->readOnly(),
                ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('device_id')
                    ->label('Device ID')
                    ->searchable()
                    ->font('mono'),
                Tables\Columns\TextColumn::make('device_name')
                    ->label('Nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('vehicle.name')
                    ->label('Kendaraan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('brand')
                    ->label('Merk')
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('sim_provider')
                    ->label('SIM Provider')
                    ->color('gray'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'active' => 'success',
                        'inactive' => 'gray',
                        'maintenance' => 'warning',
                        'lost' => 'danger',
                    }),
                Tables\Columns\IconColumn::make('is_online')
                    ->label('Online')
                    ->boolean()
                    ->getStateUsing(fn (GpsTracker $record): bool => $record->isOnline()),
                Tables\Columns\TextColumn::make('last_speed')
                    ->label('Speed')
                    ->suffix(' km/h')
                    ->sortable(),
                Tables\Columns\TextColumn::make('last_battery_level')
                    ->label('Battery')
                    ->suffix('%')
                    ->sortable()
                    ->color(fn (?int $state) => match (true) {
                        ($state ?? 0) < 20 => 'danger',
                        ($state ?? 0) < 50 => 'warning',
                        default => 'success',
                    }),
                Tables\Columns\TextColumn::make('last_update_at')
                    ->label('Terakhir Update')
                    ->dateTime('d M H:i')
                    ->sortable()
                    ->color(fn (?GpsTracker $record) => $record?->isOnline() ? 'success' : 'gray'),
            ])
            ->defaultSort('last_update_at', 'desc')
            ->actions([
                Actions\EditAction::make(),
                Actions\Action::make('viewOnMap')
                    ->label('Lihat di Peta')
                    ->icon('heroicon-o-map')
                    ->color('info')
                    ->url(fn (GpsTracker $record) => $record->last_latitude
                        ? "https://www.google.com/maps?q={$record->last_latitude},{$record->last_longitude}"
                        : '#')
                    ->openInNewTab()
                    ->visible(fn (GpsTracker $record) => $record->last_latitude !== null),
            ])
            ->bulkActions([
                Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGpsTrackers::route('/'),
            'create' => Pages\CreateGpsTracker::route('/create'),
            'edit' => Pages\EditGpsTracker::route('/{record}/edit'),
        ];
    }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceScheduleResource\Pages;
use App\Models\ServiceSchedule;
use Filament\Forms;
use Filament\Schemas;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;
use BackedEnum;

class ServiceScheduleResource extends Resource
{
    protected static ?string $model = ServiceSchedule::class;

    protected static BackedEnum | string | null $navigationIcon = 'heroicon-o-clock';

    protected static string | UnitEnum | null $navigationGroup = '🔧 Operasional';

    protected static ?int $navigationSort = 32;

    protected static ?string $navigationLabel = 'Jadwal Servis';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Schemas\Components\Section::make('Jadwal Servis')->schema([
                Forms\Components\Select::make('vehicle_id')
                    ->label('Kendaraan')
                    ->relationship('vehicle', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make('service_type')
                    ->label('Jenis Servis')
                    ->required()
                    ->maxLength(100),
                Forms\Components\TextInput::make('interval_km')
                    ->label('Interval (KM)')
                    ->numeric(),
                Forms\Components\TextInput::make('interval_days')
                    ->label('Interval (Hari)')
                    ->numeric(),
                Forms\Components\TextInput::make('last_service_km')
                    ->label('Servis Terakhir (KM)')
                    ->numeric(),
                Forms\Components\DatePicker::make('last_service_date')
                    ->label('Servis Terakhir (Tanggal)'),
                Forms\Components\TextInput::make('next_service_km')
                    ->label('Servis Berikutnya (KM)')
                    ->numeric(),
                Forms\Components\DatePicker::make('next_service_date')
                    ->label('Servis Berikutnya (Tanggal)'),
                Forms\Components\Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
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
                Tables\Columns\TextColumn::make('vehicle.name')
                    ->label('Kendaraan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('service_type')
                    ->label('Jenis Servis')
                    ->searchable(),
                Tables\Columns\TextColumn::make('next_service_date')
                    ->label('Servis Berikutnya')
                    ->date('d M Y')
                    ->sortable()
                    ->color(fn ($state) => $state && $state->isPast() ? 'danger' : null),
                Tables\Columns\TextColumn::make('next_service_km')
                    ->label('KM Berikutnya')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('Aktif'),
            ])
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
            'index' => Pages\ListServiceSchedules::route('/'),
            'create' => Pages\CreateServiceSchedule::route('/create'),
            'edit' => Pages\EditServiceSchedule::route('/{record}/edit'),
            'view' => Pages\ViewServiceSchedule::route('/{record}'),
        ];
    }
}

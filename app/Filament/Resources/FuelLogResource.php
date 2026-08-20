<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FuelLogResource\Pages;
use App\Models\FuelLog;
use Filament\Forms;
use Filament\Schemas;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;
use BackedEnum;

class FuelLogResource extends Resource
{
    protected static ?string $model = FuelLog::class;

    protected static BackedEnum | string | null $navigationIcon = 'heroicon-o-bolt';

    protected static string | UnitEnum | null $navigationGroup = '🔧 Operasional';

    protected static ?int $navigationSort = 33;

    protected static ?string $navigationLabel = 'Log Bahan Bakar';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Schemas\Components\Section::make('Informasi Pengisian BBM')->schema([
                Forms\Components\Select::make('vehicle_id')
                    ->label('Kendaraan')
                    ->relationship('vehicle', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\DatePicker::make('fuel_date')
                    ->label('Tanggal')
                    ->required(),
                Forms\Components\Select::make('fuel_type')
                    ->label('Jenis BBM')
                    ->options([
                        'gasoline' => 'Bensin',
                        'diesel' => 'Diesel',
                        'electric' => 'Listrik',
                        'hybrid' => 'Hybrid',
                        'lpg' => 'LPG',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('liters')
                    ->label('Liter')
                    ->numeric()
                    ->required()
                    ->step(0.01),
                Forms\Components\TextInput::make('cost_per_liter')
                    ->label('Harga/Liter (Rp)')
                    ->numeric()
                    ->prefix('Rp'),
                Forms\Components\TextInput::make('total_cost')
                    ->label('Total Biaya (Rp)')
                    ->numeric()
                    ->prefix('Rp'),
                Forms\Components\TextInput::make('odometer_km')
                    ->label('Odometer (KM)')
                    ->numeric()
                    ->required(),
                Forms\Components\TextInput::make('station_name')
                    ->label('Nama SPBU')
                    ->maxLength(255),
                Forms\Components\Toggle::make('full_tank')
                    ->label('Full Tank')
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
                Tables\Columns\TextColumn::make('fuel_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('fuel_type')
                    ->label('Jenis BBM')
                    ->sortable(),
                Tables\Columns\TextColumn::make('liters')
                    ->label('Liter')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_cost')
                    ->label('Biaya')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('odometer_km')
                    ->label('Odometer')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('fuel_date')
                    ->label('Tanggal')
                    ->form([
                        Forms\Components\DatePicker::make('date_from')->label('Dari'),
                        Forms\Components\DatePicker::make('date_until')->label('Sampai'),
                    ])
                    ->query(function ($query, array $data): void {
                        $query->when($data['date_from'], fn ($q, $date) => $q->where('fuel_date', '>=', $date));
                        $query->when($data['date_until'], fn ($q, $date) => $q->where('fuel_date', '<=', $date));
                    }),
            ])
            ->actions([
                Filament\Actions\EditAction::make(),
                Filament\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Filament\Actions\BulkActionGroup::make([
                    Filament\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFuelLogs::route('/'),
            'create' => Pages\CreateFuelLog::route('/create'),
            'edit' => Pages\EditFuelLog::route('/{record}/edit'),
            'view' => Pages\ViewFuelLog::route('/{record}'),
        ];
    }
}

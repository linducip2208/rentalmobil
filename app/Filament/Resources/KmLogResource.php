<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KmLogResource\Pages;
use App\Models\KmLog;
use Filament\Forms;
use Filament\Schemas;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Actions;
use Filament\Tables\Table;
use UnitEnum;
use BackedEnum;

class KmLogResource extends Resource
{
    protected static ?string $model = KmLog::class;

    protected static BackedEnum | string | null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static string | UnitEnum | null $navigationGroup = '🛠️ Perawatan Armada';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Catatan Kilometer';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Schemas\Components\Section::make('Data KM')->schema([
                Forms\Components\Select::make('vehicle_id')
                    ->label('Kendaraan')
                    ->relationship('vehicle', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('logged_by')
                    ->label('Dicatat Oleh')
                    ->relationship('loggedBy', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\DatePicker::make('log_date')
                    ->label('Tanggal')
                    ->required(),
                Forms\Components\TextInput::make('start_km')
                    ->label('KM Awal')
                    ->numeric()
                    ->required(),
                Forms\Components\TextInput::make('end_km')
                    ->label('KM Akhir')
                    ->numeric()
                    ->required(),
                Forms\Components\TextInput::make('purpose')
                    ->label('Tujuan')
                    ->maxLength(255),
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
                    ->searchable(),
                Tables\Columns\TextColumn::make('loggedBy.name')
                    ->label('Dicatat Oleh'),
                Tables\Columns\TextColumn::make('log_date')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_km')
                    ->label('KM Awal')
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_km')
                    ->label('KM Akhir')
                    ->sortable(),
                Tables\Columns\TextColumn::make('distance')
                    ->label('Jarak')
                    ->suffix(' km')
                    ->sortable(),
                Tables\Columns\TextColumn::make('purpose')
                    ->label('Tujuan')
                    ->searchable(),
            ])
            ->defaultSort('log_date', 'desc')
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
            'index' => Pages\ListKmLogs::route('/'),
            'create' => Pages\CreateKmLog::route('/create'),
            'edit' => Pages\EditKmLog::route('/{record}/edit'),
            'view' => Pages\ViewKmLog::route('/{record}'),
        ];
    }
}

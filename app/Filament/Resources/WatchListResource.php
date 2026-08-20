<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WatchListResource\Pages;
use App\Models\WatchList;
use Filament\Forms;
use Filament\Schemas;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Actions;
use Filament\Tables\Table;
use UnitEnum;
use BackedEnum;

class WatchListResource extends Resource
{
    protected static ?string $model = WatchList::class;

    protected static BackedEnum | string | null $navigationIcon = 'heroicon-o-eye';

    protected static string | UnitEnum | null $navigationGroup = '🛡️ Risiko & Keamanan';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Daftar Pantau';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Schemas\Components\Section::make('Informasi Watch List')->schema([
                Forms\Components\Select::make('vehicle_id')
                    ->label('Kendaraan')
                    ->relationship('vehicle', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Textarea::make('reason')
                    ->label('Alasan')
                    ->rows(3)
                    ->required(),
                Forms\Components\Select::make('severity')
                    ->label('Tingkat Keparahan')
                    ->options([
                        'low' => 'Rendah',
                        'medium' => 'Sedang',
                        'high' => 'Tinggi',
                        'critical' => 'Kritis',
                    ])
                    ->default('medium')
                    ->required(),
                Forms\Components\Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
                Forms\Components\Select::make('added_by')
                    ->label('Ditambahkan Oleh')
                    ->relationship('addedBy', 'name')
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('resolved_by')
                    ->label('Diselesaikan Oleh')
                    ->relationship('resolvedBy', 'name')
                    ->searchable()
                    ->preload(),
                Forms\Components\DateTimePicker::make('resolved_at')
                    ->label('Waktu Diselesaikan'),
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
                Tables\Columns\TextColumn::make('reason')
                    ->label('Alasan')
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\TextColumn::make('severity')
                    ->label('Keparahan')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'low' => 'gray',
                        'medium' => 'warning',
                        'high' => 'danger',
                        'critical' => 'danger',
                    }),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                Tables\Columns\TextColumn::make('addedBy.name')
                    ->label('Ditambahkan Oleh'),
                Tables\Columns\TextColumn::make('resolvedBy.name')
                    ->label('Diselesaikan Oleh'),
                Tables\Columns\TextColumn::make('resolved_at')
                    ->label('Waktu Selesai')
                    ->dateTime(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Aktif'),
                Tables\Filters\SelectFilter::make('severity')
                    ->label('Keparahan')
                    ->options([
                        'low' => 'Rendah',
                        'medium' => 'Sedang',
                        'high' => 'Tinggi',
                        'critical' => 'Kritis',
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
            'index' => Pages\ListWatchLists::route('/'),
            'create' => Pages\CreateWatchList::route('/create'),
            'edit' => Pages\EditWatchList::route('/{record}/edit'),
            'view' => Pages\ViewWatchList::route('/{record}'),
        ];
    }
}

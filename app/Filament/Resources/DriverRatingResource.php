<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DriverRatingResource\Pages;
use App\Models\DriverRating;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Actions;
use Filament\Tables\Table;
use UnitEnum;
use BackedEnum;

class DriverRatingResource extends Resource
{
    protected static ?string $model = DriverRating::class;

    protected static BackedEnum | string | null $navigationIcon = 'heroicon-o-star';

    protected static string | UnitEnum | null $navigationGroup = '🔧 Operasional';

    protected static ?int $navigationSort = 12;

    protected static ?string $navigationLabel = 'Rating Driver';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\Section::make('Rating')
                ->schema([
                    Forms\Components\Select::make('driver_id')
                        ->label('Driver')
                        ->relationship('driver', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),
                    Forms\Components\Select::make('rental_order_id')
                        ->label('Rental Order')
                        ->relationship('rentalOrder', 'order_number')
                        ->searchable()
                        ->preload()
                        ->nullable(),
                    Forms\Components\Select::make('customer_id')
                        ->label('Pelanggan')
                        ->relationship('customer', 'name')
                        ->searchable()
                        ->preload()
                        ->nullable(),
                    Forms\Components\Select::make('rating')
                        ->label('Rating Utama')
                        ->options([
                            1 => '1 - Sangat Buruk',
                            2 => '2 - Buruk',
                            3 => '3 - Cukup',
                            4 => '4 - Baik',
                            5 => '5 - Sangat Baik',
                        ])
                        ->required(),
                    Forms\Components\Toggle::make('is_anonymous')
                        ->label('Anonymous'),
                ])->columns(2),

            Forms\Components\Section::make('Detail Rating')
                ->schema([
                    Forms\Components\Select::make('punctuality')
                        ->label('Ketepatan Waktu')
                        ->options([1=>1,2=>2,3=>3,4=>4,5=>5])
                        ->nullable(),
                    Forms\Components\Select::make('driving_skill')
                        ->label('Keterampilan Mengemudi')
                        ->options([1=>1,2=>2,3=>3,4=>4,5=>5])
                        ->nullable(),
                    Forms\Components\Select::make('attitude')
                        ->label('Sikap / Pelayanan')
                        ->options([1=>1,2=>2,3=>3,4=>4,5=>5])
                        ->nullable(),
                    Forms\Components\Select::make('vehicle_cleanliness')
                        ->label('Kebersihan Kendaraan')
                        ->options([1=>1,2=>2,3=>3,4=>4,5=>5])
                        ->nullable(),
                    Forms\Components\Textarea::make('comment')
                        ->label('Komentar')
                        ->rows(3),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('driver.name')
                    ->label('Driver')
                    ->searchable(),
                Tables\Columns\TextColumn::make('rentalOrder.order_number')
                    ->label('Order')
                    ->searchable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Pelanggan')
                    ->searchable()
                    ->formatStateUsing(fn($state, DriverRating $record) => $record->is_anonymous ? '(Anonymous)' : $state),
                Tables\Columns\TextColumn::make('rating')
                    ->label('Rating')
                    ->badge()
                    ->color(fn(int $state) => match(true) {
                        $state >= 4 => 'success',
                        $state === 3 => 'warning',
                        default => 'danger',
                    })
                    ->formatStateUsing(fn(int $state) => str_repeat('⭐', $state)),
                Tables\Columns\TextColumn::make('punctuality')
                    ->label('Tepat Waktu')
                    ->sortable()
                    ->formatStateUsing(fn(?int $state) => $state ? str_repeat('★', $state) : '-'),
                Tables\Columns\TextColumn::make('driving_skill')
                    ->label('Skill')
                    ->sortable()
                    ->formatStateUsing(fn(?int $state) => $state ? str_repeat('★', $state) : '-'),
                Tables\Columns\TextColumn::make('attitude')
                    ->label('Sikap')
                    ->sortable()
                    ->formatStateUsing(fn(?int $state) => $state ? str_repeat('★', $state) : '-'),
                Tables\Columns\TextColumn::make('comment')
                    ->label('Komentar')
                    ->limit(50)
                    ->wrap(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->actions([
                Actions\EditAction::make(),
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
            'index' => Pages\ListDriverRatings::route('/'),
            'create' => Pages\CreateDriverRating::route('/create'),
            'edit' => Pages\EditDriverRating::route('/{record}/edit'),
        ];
    }
}

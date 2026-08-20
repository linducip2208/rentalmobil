<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeliveryResource\Pages;
use App\Models\Delivery;
use Filament\Forms;
use Filament\Schemas;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;
use BackedEnum;

class DeliveryResource extends Resource
{
    protected static ?string $model = Delivery::class;

    protected static BackedEnum | string | null $navigationIcon = 'heroicon-o-truck';

    protected static string | UnitEnum | null $navigationGroup = '🔧 Operasional';

    protected static ?int $navigationSort = 34;

    protected static ?string $navigationLabel = 'Pengiriman';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Schemas\Components\Section::make('Informasi Pengiriman')->schema([
                Forms\Components\Select::make('rental_order_id')
                    ->label('Order Sewa')
                    ->relationship('rentalOrder', 'order_number')
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('driver_id')
                    ->label('Supir')
                    ->relationship('driver', 'name')
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('vehicle_id')
                    ->label('Kendaraan')
                    ->relationship('vehicle', 'name')
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('from_location_id')
                    ->label('Dari Lokasi')
                    ->relationship('fromLocation', 'name')
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('to_location_id')
                    ->label('Ke Lokasi')
                    ->relationship('toLocation', 'name')
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('delivery_type')
                    ->label('Jenis Pengiriman')
                    ->options([
                        'pickup' => 'Jemput',
                        'delivery' => 'Antar',
                        'transfer' => 'Transfer',
                        'return' => 'Pengembalian',
                    ])
                    ->required(),
                Forms\Components\DateTimePicker::make('scheduled_date')
                    ->label('Jadwal')
                    ->required(),
                Forms\Components\DateTimePicker::make('actual_date')
                    ->label('Tanggal Aktual'),
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Menunggu',
                        'scheduled' => 'Dijadwalkan',
                        'in_progress' => 'Dikerjakan',
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
                Tables\Columns\TextColumn::make('rentalOrder.order_number')
                    ->label('No. Order')
                    ->sortable(),
                Tables\Columns\TextColumn::make('driver.name')
                    ->label('Supir')
                    ->sortable(),
                Tables\Columns\TextColumn::make('delivery_type')
                    ->label('Jenis')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pickup' => 'info',
                        'delivery' => 'success',
                        'transfer' => 'warning',
                        'return' => 'primary',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pickup' => 'Jemput',
                        'delivery' => 'Antar',
                        'transfer' => 'Transfer',
                        'return' => 'Pengembalian',
                        default => ucfirst($state),
                    }),
                Tables\Columns\TextColumn::make('scheduled_date')
                    ->label('Jadwal')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'scheduled' => 'info',
                        'in_progress' => 'warning',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Menunggu',
                        'scheduled' => 'Dijadwalkan',
                        'in_progress' => 'Dikerjakan',
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
                        'scheduled' => 'Dijadwalkan',
                        'in_progress' => 'Dikerjakan',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                    ]),
                Tables\Filters\SelectFilter::make('delivery_type')
                    ->label('Jenis')
                    ->options([
                        'pickup' => 'Jemput',
                        'delivery' => 'Antar',
                        'transfer' => 'Transfer',
                        'return' => 'Pengembalian',
                    ]),
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
            'index' => Pages\ListDeliverys::route('/'),
            'create' => Pages\CreateDelivery::route('/create'),
            'edit' => Pages\EditDelivery::route('/{record}/edit'),
            'view' => Pages\ViewDelivery::route('/{record}'),
        ];
    }
}

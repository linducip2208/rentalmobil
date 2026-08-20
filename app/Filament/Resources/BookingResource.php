<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookingResource\Pages;
use App\Models\Booking;
use Filament\Forms;
use Filament\Schemas;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;
use BackedEnum;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static BackedEnum | string | null $navigationIcon = 'heroicon-o-calendar';

    protected static string | UnitEnum | null $navigationGroup = '📋 Penjualan';

    protected static ?int $navigationSort = 11;

    protected static ?string $navigationLabel = 'Booking';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Schemas\Components\Section::make('Informasi Booking')->schema([
                Forms\Components\Select::make('customer_id')
                    ->label('Customer')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('vehicle_id')
                    ->label('Kendaraan')
                    ->relationship('vehicle', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('driver_id')
                    ->label('Supir')
                    ->relationship('driver', 'name')
                    ->searchable()
                    ->preload()
                    ->placeholder('— Tanpa supir —'),
                Forms\Components\Select::make('pickup_location_id')
                    ->label('Lokasi Pengambilan')
                    ->relationship('pickupLocation', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('return_location_id')
                    ->label('Lokasi Pengembalian')
                    ->relationship('returnLocation', 'name')
                    ->searchable()
                    ->preload(),
            ])->columns(2),

            Schemas\Components\Section::make('Jadwal')->schema([
                Forms\Components\DateTimePicker::make('start_date')
                    ->label('Tanggal Mulai')
                    ->required(),
                Forms\Components\DateTimePicker::make('end_date')
                    ->label('Tanggal Selesai')
                    ->required(),
                Forms\Components\DatePicker::make('pickup_date')
                    ->label('Tanggal Ambil'),
                Forms\Components\DatePicker::make('return_date')
                    ->label('Tanggal Kembali'),
                Forms\Components\TextInput::make('duration_days')
                    ->label('Durasi (Hari)')
                    ->numeric()
                    ->default(1),
            ])->columns(2),

            Schemas\Components\Section::make('Biaya')->schema([
                Forms\Components\TextInput::make('daily_rate')
                    ->label('Tarif/Hari (Rp)')
                    ->numeric()
                    ->prefix('Rp'),
                Forms\Components\TextInput::make('subtotal')
                    ->label('Subtotal (Rp)')
                    ->numeric()
                    ->prefix('Rp'),
                Forms\Components\TextInput::make('discount_amount')
                    ->label('Diskon (Rp)')
                    ->numeric()
                    ->prefix('Rp')
                    ->default(0),
                Forms\Components\TextInput::make('tax_amount')
                    ->label('Pajak (Rp)')
                    ->numeric()
                    ->prefix('Rp')
                    ->default(0),
                Forms\Components\TextInput::make('total_amount')
                    ->label('Total (Rp)')
                    ->numeric()
                    ->prefix('Rp'),
                Forms\Components\TextInput::make('deposit_amount')
                    ->label('Deposit (Rp)')
                    ->numeric()
                    ->prefix('Rp'),
            ])->columns(3),

            Schemas\Components\Section::make('Status & Catatan')->schema([
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Menunggu',
                        'confirmed' => 'Dikonfirmasi',
                        'active' => 'Aktif',
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
                Tables\Columns\TextColumn::make('booking_number')
                    ->label('No. Booking')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('vehicle.name')
                    ->label('Kendaraan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Mulai')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('Selesai')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'confirmed' => 'info',
                        'active' => 'success',
                        'completed' => 'gray',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Menunggu',
                        'confirmed' => 'Dikonfirmasi',
                        'active' => 'Aktif',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                        default => ucfirst($state),
                    }),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Menunggu',
                        'confirmed' => 'Dikonfirmasi',
                        'active' => 'Aktif',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                    ]),
                Tables\Filters\Filter::make('start_date')
                    ->label('Tanggal Mulai')
                    ->form([
                        Forms\Components\DatePicker::make('start_from')
                            ->label('Dari'),
                        Forms\Components\DatePicker::make('start_until')
                            ->label('Sampai'),
                    ])
                    ->query(function ($query, array $data): void {
                        $query->when($data['start_from'], fn ($q, $date) => $q->where('start_date', '>=', $date));
                        $query->when($data['start_until'], fn ($q, $date) => $q->where('start_date', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('convertToOrder')
                    ->label('Konversi ke Order')
                    ->icon('heroicon-o-arrow-right-square')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Konversi Booking ke Order')
                    ->modalDescription('Apakah Anda yakin ingin mengkonversi booking ini menjadi order?')
                    ->modalSubmitActionLabel('Ya, Konversi')
                    ->action(function (Booking $record) {
                        $order = \App\Models\RentalOrder::create([
                            'booking_id' => $record->id,
                            'customer_id' => $record->customer_id,
                            'vehicle_id' => $record->vehicle_id,
                            'driver_id' => $record->driver_id,
                            'location_id' => $record->pickup_location_id,
                            'start_date' => $record->start_date,
                            'end_date' => $record->end_date,
                            'rental_type' => $record->rental_type,
                            'duration_days' => $record->duration_days,
                            'daily_rate_snapshot' => $record->daily_rate_snapshot,
                            'subtotal' => $record->subtotal,
                            'discount_total' => $record->discount_amount,
                            'tax_total' => $record->tax_amount,
                            'final_amount' => $record->total_amount,
                            'deposit_amount' => $record->deposit_amount,
                            'balance_due' => $record->total_amount - $record->deposit_amount,
                            'status' => 'draft',
                            'payment_status' => $record->deposit_amount > 0 ? 'partial' : 'unpaid',
                            'created_by' => auth()->id(),
                        ]);
                        $record->update(['status' => 'converted']);
                        \Filament\Notifications\Notification::make()
                            ->title("Berhasil dikonversi! Order #{$order->order_number}")
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Booking $record): bool => $record->status === 'confirmed'),
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
            'index' => Pages\ListBookings::route('/'),
            'create' => Pages\CreateBooking::route('/create'),
            'edit' => Pages\EditBooking::route('/{record}/edit'),
            'view' => Pages\ViewBooking::route('/{record}'),
        ];
    }
}

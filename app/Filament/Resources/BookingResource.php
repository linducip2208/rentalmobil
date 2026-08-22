<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookingResource\Pages;
use App\Models\Booking;
use Filament\Forms;
use Filament\Schemas;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Actions;
use Filament\Tables\Table;
use UnitEnum;
use BackedEnum;
use App\Services\ApprovalService;
use App\Services\BookingService;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static BackedEnum | string | null $navigationIcon = 'heroicon-o-calendar';

    protected static string | UnitEnum | null $navigationGroup = '📅 Reservasi & Rental';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Reservasi';

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
                Forms\Components\TextInput::make('purchase_order_number')
                    ->label('No. PO Perusahaan')
                    ->maxLength(80)
                    ->placeholder('PO-2026-001')
                    ->helperText('Untuk customer korporat yang memesan via purchase order.'),
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
                Forms\Components\DatePicker::make('estimated_return_date')
                    ->label('Estimasi Pengembalian'),
                Forms\Components\TextInput::make('duration_days')
                    ->label('Durasi (Hari)')
                    ->numeric()
                    ->default(1),
            ])->columns(2),

            Schemas\Components\Section::make('Biaya')->schema([
                Forms\Components\TextInput::make('daily_rate_snapshot')
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
                    ->modalForm([
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
                Actions\Action::make('convertToOrder')
                    ->label('Konversi ke Order')
                    ->icon('heroicon-o-arrow-right-end-on-rectangle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Konversi Booking ke Order')
                    ->modalDescription('Apakah Anda yakin ingin mengkonversi booking ini menjadi order?')
                    ->modalSubmitActionLabel('Ya, Konversi')
                    ->action(function (Booking $record) {
                        $approval = app(ApprovalService::class);
                        if ($approval->checkApprovalRequired('booking', (float) $record->total_amount)) {
                            $approval->submitForApproval($record, 'booking', auth()->id());
                            \Filament\Notifications\Notification::make()->title('Booking dikirim untuk persetujuan')->warning()->send();
                            return;
                        }
                        $order = app(BookingService::class)->convertToOrder($record);
                        \Filament\Notifications\Notification::make()
                            ->title("Berhasil dikonversi! Order #{$order->order_number}")
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Booking $record): bool => $record->status === 'confirmed'),
                Actions\Action::make('confirm')
                    ->label('Konfirmasi')->icon('heroicon-o-check-circle')->color('info')->requiresConfirmation()
                    ->action(fn (Booking $record) => app(BookingService::class)->confirmBooking($record))
                    ->visible(fn (Booking $record): bool => $record->status === 'pending_verification'),
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
            'index' => Pages\ListBookings::route('/'),
            'create' => Pages\CreateBooking::route('/create'),
            'edit' => Pages\EditBooking::route('/{record}/edit'),
            'view' => Pages\ViewBooking::route('/{record}'),
        ];
    }
}

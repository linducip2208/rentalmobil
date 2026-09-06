<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookingResource\Pages;
use App\Filament\Resources\EnterpriseResource as Resource;
use App\Models\Booking;
use App\Models\Vehicle;
use App\Services\ApprovalService;
use App\Services\BookingService;
use BackedEnum;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Schemas;
use Filament\Schemas\Get;
use Filament\Schemas\Schema;
use Filament\Schemas\Set;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-calendar';

    protected static string|UnitEnum|null $navigationGroup = 'Rental';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Reservasi';

    /**
     * Booking statuses aligned with BookingService domain transitions.
     */
    private static function statusOptions(): array
    {
        return [
            'hold' => 'Ditahan Sementara',
            'pending_verification' => 'Menunggu Verifikasi',
            'pending_payment' => 'Menunggu Pembayaran',
            'confirmed' => 'Dikonfirmasi',
            'converted' => 'Menjadi Order',
            'expired' => 'Kedaluwarsa',
            'cancelled' => 'Dibatalkan',
        ];
    }

    public static function recalcBookingTotals(Get $get, Set $set): void
    {
        try {
            $start = $get('start_date') ? Carbon::parse($get('start_date')) : null;
            $end = $get('end_date') ? Carbon::parse($get('end_date')) : null;
            $days = ($start && $end) ? max(1, $start->diffInDays($end)) : (int) ($get('duration_days') ?: 1);
            $set('duration_days', $days);

            $rate = (float) ($get('daily_rate_snapshot') ?: 0);
            $subtotal = round($days * $rate, 2);
            $set('subtotal', $subtotal);

            $discount = (float) ($get('discount_amount') ?: 0);
            $tax = (float) ($get('tax_amount') ?: 0);
            // Pajak otomatis 11% dari (subtotal-diskon) bila masih 0 dan ada subtotal.
            if ($tax <= 0 && $subtotal > 0) {
                $tax = round(max(0, $subtotal - $discount) * 0.11, 2);
                $set('tax_amount', $tax);
            }
            $set('total_amount', round(max(0, $subtotal - $discount) + $tax, 2));
        } catch (\Throwable) {
            // Best-effort: jangan blokir form bila tanggal belum valid.
        }
    }

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
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (Set $set, Get $get, $state) {
                        if ($state && ($v = Vehicle::find($state))) {
                            $set('daily_rate_snapshot', (float) $v->daily_rate);
                            if (! $get('deposit_amount')) {
                                $set('deposit_amount', (float) ($v->deposit_amount ?? 0));
                            }
                        }
                        self::recalcBookingTotals($get, $set);
                    }),
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
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn (Set $set, Get $get) => self::recalcBookingTotals($get, $set)),
                Forms\Components\DateTimePicker::make('end_date')
                    ->label('Tanggal Selesai')
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn (Set $set, Get $get) => self::recalcBookingTotals($get, $set)),
                Forms\Components\DatePicker::make('estimated_return_date')
                    ->label('Estimasi Pengembalian'),
                Forms\Components\TextInput::make('duration_days')
                    ->label('Durasi (Hari)')
                    ->numeric()
                    ->default(1)
                    ->readOnly()
                    ->helperText('Otomatis dari tanggal mulai & selesai.'),
            ])->columns(2),

            Schemas\Components\Section::make('Biaya')->description('Otomatis dari kendaraan & tanggal — masih bisa dikoreksi manual.')->schema([
                Forms\Components\TextInput::make('daily_rate_snapshot')
                    ->label('Tarif/Hari (Rp)')
                    ->numeric()
                    ->prefix('Rp')
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set, Get $get) => self::recalcBookingTotals($get, $set)),
                Forms\Components\TextInput::make('subtotal')
                    ->label('Subtotal (Rp)')
                    ->numeric()
                    ->prefix('Rp')
                    ->readOnly()
                    ->helperText('Durasi × tarif harian.'),
                Forms\Components\TextInput::make('discount_amount')
                    ->label('Diskon (Rp)')
                    ->numeric()
                    ->prefix('Rp')
                    ->default(0)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set, Get $get) => self::recalcBookingTotals($get, $set)),
                Forms\Components\TextInput::make('tax_amount')
                    ->label('Pajak (Rp)')
                    ->numeric()
                    ->prefix('Rp')
                    ->default(0)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set, Get $get) => self::recalcBookingTotals($get, $set)),
                Forms\Components\TextInput::make('total_amount')
                    ->label('Total (Rp)')
                    ->numeric()
                    ->prefix('Rp')
                    ->readOnly()
                    ->helperText('Subtotal − diskon + pajak.'),
                Forms\Components\TextInput::make('deposit_amount')
                    ->label('Deposit (Rp)')
                    ->numeric()
                    ->prefix('Rp'),
            ])->columns(3),

            Schemas\Components\Section::make('Status & Catatan')->schema([
                // Status transitions happen ONLY through table actions backed
                // by BookingService (verify → confirm → convert / cancel).
                // The select is create-only: admin starts a booking as hold.
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'hold' => 'Ditahan Sementara',
                        'pending_verification' => 'Menunggu Verifikasi',
                    ])
                    ->default('hold')
                    ->hiddenOn('edit')
                    ->dehydrated(fn (string $operation) => $operation === 'create')
                    ->required(),
                Forms\Components\Placeholder::make('status_display')
                    ->label('Status')
                    ->content(fn (?Booking $record) => self::statusOptions()[$record?->status] ?? $record?->status)
                    ->visibleOn('edit'),
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
                        'hold' => 'gray',
                        'pending_verification', 'pending_payment' => 'warning',
                        'confirmed' => 'info',
                        'converted' => 'success',
                        'expired' => 'gray',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => self::statusOptions()[$state] ?? ucfirst($state)),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(self::statusOptions()),
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
                            Notification::make()->title('Booking dikirim untuk persetujuan')->warning()->send();

                            return;
                        }
                        try {
                            $order = app(BookingService::class)->convertToOrder($record);
                            Notification::make()
                                ->title("Berhasil dikonversi! Order #{$order->order_number}")
                                ->success()
                                ->send();
                        } catch (\RuntimeException $e) {
                            Notification::make()->title('Gagal konversi')->body($e->getMessage())->danger()->send();
                        }
                    })
                    ->visible(fn (Booking $record): bool => in_array($record->status, ['pending_verification', 'confirmed'], true)),
                Actions\Action::make('confirm')
                    ->label('Konfirmasi')->icon('heroicon-o-check-circle')->color('info')->requiresConfirmation()
                    ->action(function (Booking $record) {
                        try {
                            app(BookingService::class)->confirmBooking($record);
                            Notification::make()->title('Booking dikonfirmasi')->success()->send();
                        } catch (\RuntimeException $e) {
                            Notification::make()->title('Gagal konfirmasi')->body($e->getMessage())->danger()->send();
                        }
                    })
                    ->visible(fn (Booking $record): bool => $record->status === 'pending_verification'),
                Actions\Action::make('verify')
                    ->label('Verifikasi Pembayaran')
                    ->icon('heroicon-o-banknotes')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (Booking $record): bool => $record->status === 'pending_payment')
                    ->action(function (Booking $record) {
                        $record->update(['status' => 'pending_verification']);
                        Notification::make()->title('Booking siap diverifikasi')->success()->send();
                    }),
                Actions\Action::make('cancel')
                    ->label('Batalkan')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('reason')
                            ->label('Alasan pembatalan')
                            ->required()
                            ->maxLength(500),
                    ])
                    ->action(function (Booking $record, array $data) {
                        try {
                            app(BookingService::class)->cancelBooking($record, $data['reason']);
                            Notification::make()->title('Booking dibatalkan')->warning()->send();
                        } catch (\RuntimeException $e) {
                            Notification::make()->title('Gagal membatalkan')->body($e->getMessage())->danger()->send();
                        }
                    })
                    ->visible(fn (Booking $record): bool => ! in_array($record->status, ['converted', 'cancelled', 'expired'], true)),
                Actions\EditAction::make()
                    ->visible(fn (Booking $record): bool => ! in_array($record->status, ['converted', 'cancelled', 'expired'], true)),
                Actions\DeleteAction::make()
                    ->visible(fn (Booking $record): bool => in_array($record->status, ['cancelled', 'expired'], true)),
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

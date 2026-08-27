<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EnterpriseResource as Resource;
use App\Filament\Resources\RentalOrderResource\Pages;
use App\Models\RentalOrder;
use App\Services\DepositRefundService;
use App\Services\HandoverLinkService;
use BackedEnum;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Notifications\Notification;
use Filament\Schemas;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class RentalOrderResource extends Resource
{
    protected static ?string $model = RentalOrder::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static string|UnitEnum|null $navigationGroup = 'Rental';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationLabel = 'Order Sewa';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Schemas\Components\Section::make('Informasi Order')->schema([
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
                TextInput::make('purchase_order_number')
                    ->label('No. PO Perusahaan')
                    ->maxLength(80)
                    ->placeholder('PO-2026-001'),
                Forms\Components\Select::make('driver_id')
                    ->label('Supir')
                    ->relationship('driver', 'name')
                    ->searchable()
                    ->preload()
                    ->placeholder('â€” Tanpa supir â€”'),
                Forms\Components\Select::make('location_id')
                    ->label('Lokasi')
                    ->relationship('location', 'name')
                    ->searchable()
                    ->preload(),
            ])->columns(2),

            Schemas\Components\Section::make('Jadwal Sewa')->schema([
                Forms\Components\DateTimePicker::make('start_date')
                    ->label('Tanggal Mulai')
                    ->required(),
                Forms\Components\DateTimePicker::make('end_date')
                    ->label('Tanggal Selesai')
                    ->required(),
                Forms\Components\DateTimePicker::make('actual_return_date')
                    ->label('Tanggal Kembali Aktual'),
                TextInput::make('duration_days')
                    ->label('Durasi (Hari)')
                    ->numeric()
                    ->default(1),
            ])->columns(2),

            Schemas\Components\Section::make('Biaya')->schema([
                TextInput::make('daily_rate')
                    ->label('Tarif/Hari (Rp)')
                    ->numeric()
                    ->prefix('Rp'),
                TextInput::make('subtotal')
                    ->label('Subtotal (Rp)')
                    ->numeric()
                    ->prefix('Rp'),
                TextInput::make('addon_total')
                    ->label('Add-on (Rp)')
                    ->numeric()
                    ->prefix('Rp')
                    ->default(0),
                TextInput::make('discount_amount')
                    ->label('Diskon (Rp)')
                    ->numeric()
                    ->prefix('Rp')
                    ->default(0),
                TextInput::make('tax_amount')
                    ->label('Pajak (Rp)')
                    ->numeric()
                    ->prefix('Rp')
                    ->default(0),
                TextInput::make('late_fee')
                    ->label('Denda Keterlambatan (Rp)')
                    ->numeric()
                    ->prefix('Rp')
                    ->default(0),
                TextInput::make('damage_fee')
                    ->label('Biaya Kerusakan (Rp)')
                    ->numeric()
                    ->prefix('Rp')
                    ->default(0),
                TextInput::make('total_amount')
                    ->label('Total (Rp)')
                    ->numeric()
                    ->prefix('Rp'),
                TextInput::make('amount_paid')
                    ->label('Sudah Dibayar (Rp)')
                    ->numeric()
                    ->prefix('Rp')
                    ->default(0),
                TextInput::make('deposit_amount')
                    ->label('Deposit (Rp)')
                    ->numeric()
                    ->prefix('Rp'),
                Forms\Components\Toggle::make('deposit_refunded')
                    ->label('Deposit Dikembalikan')
                    ->default(false),
            ])->columns(3),

            Schemas\Components\Section::make('Status & Catatan')->schema([
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Draft',
                        'confirmed' => 'Dikonfirmasi',
                        'active' => 'Aktif',
                        'overdue' => 'Terlambat',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                    ])
                    ->default('draft')
                    ->required(),
                Forms\Components\Select::make('payment_status')
                    ->label('Status Pembayaran')
                    ->options([
                        'unpaid' => 'Belum Bayar',
                        'partial' => 'Sebagian',
                        'paid' => 'Lunas',
                        'refunded' => 'Refund',
                    ])
                    ->default('unpaid')
                    ->required(),
                Forms\Components\Textarea::make('notes')
                    ->label('Catatan')
                    ->rows(2),
                Forms\Components\Textarea::make('internal_notes')
                    ->label('Catatan Internal')
                    ->rows(2),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('No. Order')
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
                    ->dateTime('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('Selesai')
                    ->dateTime('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'confirmed' => 'info',
                        'active' => 'success',
                        'overdue' => 'danger',
                        'completed' => 'primary',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Draft',
                        'confirmed' => 'Dikonfirmasi',
                        'active' => 'Aktif',
                        'overdue' => 'Terlambat',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                        default => ucfirst($state),
                    }),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount_paid')
                    ->label('Dibayar')
                    ->money('IDR')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Draft',
                        'confirmed' => 'Dikonfirmasi',
                        'active' => 'Aktif',
                        'overdue' => 'Terlambat',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                    ]),
                Tables\Filters\SelectFilter::make('payment_status')
                    ->label('Pembayaran')
                    ->options([
                        'unpaid' => 'Belum Bayar',
                        'partial' => 'Sebagian',
                        'paid' => 'Lunas',
                        'refunded' => 'Refund',
                    ]),
                Tables\Filters\Filter::make('start_date')
                    ->label('Tanggal')
                    ->modalForm([
                        Forms\Components\DatePicker::make('start_from')->label('Dari'),
                        Forms\Components\DatePicker::make('start_until')->label('Sampai'),
                    ])
                    ->query(function ($query, array $data): void {
                        $query->when($data['start_from'], fn ($q, $date) => $q->where('start_date', '>=', $date));
                        $query->when($data['start_until'], fn ($q, $date) => $q->where('start_date', '<=', $date));
                    }),
            ])
            ->actions([
                Actions\Action::make('checkinLink')
                    ->label('QR Check-in')
                    ->icon('heroicon-o-qr-code')
                    ->color('info')
                    ->visible(fn ($record) => ! in_array($record->status, ['checked_out', 'active', 'completed', 'cancelled'], true))
                    ->modalHeading('Self Check-in Pelanggan')
                    ->modalDescription('Bagikan QR/link ini saat serah terima â€” pelanggan foto kondisi mobil & isi BBM/odometer sendiri.')
                    ->mountUsing(function (Schema $form, $record) {
                        $link = app(HandoverLinkService::class)->issueCheckIn($record);
                        $form->fill(['link' => $link]);
                    })
                    ->form([
                        TextInput::make('link')
                            ->label('URL check-in')
                            ->readOnly()
                            ->copyable()
                            ->columnSpanFull(),
                        ViewField::make('qr')
                            ->view('filament.contracts.qr-link')
                            ->columnSpanFull(),
                    ])
                    ->action(fn () => null)
                    ->modalSubmitActionLabel('Selesai'),
                Actions\Action::make('refundDeposit')
                    ->label('Refund Deposit')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('warning')
                    ->visible(fn ($record) => $record->deposits()->whereIn('deposit_status', ['received', 'held'])->exists())
                    ->modalHeading('Pengembalian Deposit')
                    ->modalDescription('Isi potongan yang berlaku (kosongkan jika tidak ada). Invoice potongan otomatis diterbitkan.')
                    ->form([
                        TextInput::make('fuel')->numeric()->prefix('Rp')->default(0)->label('Potongan BBM'),
                        TextInput::make('cleaning')->numeric()->prefix('Rp')->default(0)->label('Biaya cuci'),
                        TextInput::make('late_fee')->numeric()->prefix('Rp')->default(0)->label('Denda keterlambatan'),
                        TextInput::make('damage')->numeric()->prefix('Rp')->default(0)->label('Kerusakan minor'),
                        TextInput::make('other')->numeric()->prefix('Rp')->default(0)->label('Potongan lain'),
                    ])
                    ->action(function ($record, array $data) {
                        $deposit = $record->deposits()->whereIn('deposit_status', ['received', 'held'])->latest()->first();
                        app(DepositRefundService::class)->refund($deposit, [
                            'fuel' => $data['fuel'] ?? 0,
                            'cleaning' => $data['cleaning'] ?? 0,
                            'late_fee' => $data['late_fee'] ?? 0,
                            'damage' => $data['damage'] ?? 0,
                            'other' => $data['other'] ?? 0,
                        ], auth()->id());
                        Notification::make()->title('Deposit dikembalikan dengan potongan tercatat')->success()->send();
                    }),
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
            'index' => Pages\ListRentalOrders::route('/'),
            'create' => Pages\CreateRentalOrder::route('/create'),
            'edit' => Pages\EditRentalOrder::route('/{record}/edit'),
            'view' => Pages\ViewRentalOrder::route('/{record}'),
        ];
    }
}

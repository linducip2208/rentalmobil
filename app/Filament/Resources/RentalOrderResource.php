<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RentalOrderResource\Pages;
use App\Models\RentalOrder;
use Filament\Forms;
use Filament\Schemas;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;
use BackedEnum;

class RentalOrderResource extends Resource
{
    protected static ?string $model = RentalOrder::class;

    protected static BackedEnum | string | null $navigationIcon = 'heroicon-o-document-text';

    protected static string | UnitEnum | null $navigationGroup = '📋 Penjualan';

    protected static ?int $navigationSort = 12;

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
                Forms\Components\Select::make('driver_id')
                    ->label('Supir')
                    ->relationship('driver', 'name')
                    ->searchable()
                    ->preload()
                    ->placeholder('— Tanpa supir —'),
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
                Forms\Components\TextInput::make('addon_total')
                    ->label('Add-on (Rp)')
                    ->numeric()
                    ->prefix('Rp')
                    ->default(0),
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
                Forms\Components\TextInput::make('late_fee')
                    ->label('Denda Keterlambatan (Rp)')
                    ->numeric()
                    ->prefix('Rp')
                    ->default(0),
                Forms\Components\TextInput::make('damage_fee')
                    ->label('Biaya Kerusakan (Rp)')
                    ->numeric()
                    ->prefix('Rp')
                    ->default(0),
                Forms\Components\TextInput::make('total_amount')
                    ->label('Total (Rp)')
                    ->numeric()
                    ->prefix('Rp'),
                Forms\Components\TextInput::make('amount_paid')
                    ->label('Sudah Dibayar (Rp)')
                    ->numeric()
                    ->prefix('Rp')
                    ->default(0),
                Forms\Components\TextInput::make('deposit_amount')
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
                    ->form([
                        Forms\Components\DatePicker::make('start_from')->label('Dari'),
                        Forms\Components\DatePicker::make('start_until')->label('Sampai'),
                    ])
                    ->query(function ($query, array $data): void {
                        $query->when($data['start_from'], fn ($q, $date) => $q->where('start_date', '>=', $date));
                        $query->when($data['start_until'], fn ($q, $date) => $q->where('start_date', '<=', $date));
                    }),
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
            'index' => Pages\ListRentalOrders::route('/'),
            'create' => Pages\CreateRentalOrder::route('/create'),
            'edit' => Pages\EditRentalOrder::route('/{record}/edit'),
            'view' => Pages\ViewRentalOrder::route('/{record}'),
        ];
    }
}

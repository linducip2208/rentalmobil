<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuotationResource\Pages;
use App\Models\Quotation;
use Filament\Forms;
use Filament\Schemas;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;
use BackedEnum;

class QuotationResource extends Resource
{
    protected static ?string $model = Quotation::class;

    protected static BackedEnum | string | null $navigationIcon = 'heroicon-o-document-duplicate';

    protected static string | UnitEnum | null $navigationGroup = '📋 Penjualan';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Kuotasi';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Schemas\Components\Section::make('Informasi Kuotasi')->schema([
                Forms\Components\TextInput::make('quotation_number')
                    ->label('No. Kuotasi')
                    ->disabled()
                    ->dehydrated(false),
                Forms\Components\Select::make('customer_id')
                    ->label('Customer')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make('customer_name')
                    ->label('Nama Customer')
                    ->maxLength(255),
                Forms\Components\TextInput::make('customer_phone')
                    ->label('Telepon Customer')
                    ->maxLength(20),
                Forms\Components\Select::make('vehicle_id')
                    ->label('Kendaraan')
                    ->relationship('vehicle', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make('vehicle_name_snapshot')
                    ->label('Snapshot Kendaraan')
                    ->maxLength(255),
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Draft',
                        'sent' => 'Terkirim',
                        'viewed' => 'Dilihat',
                        'accepted' => 'Diterima',
                        'rejected' => 'Ditolak',
                        'expired' => 'Kedaluwarsa',
                        'converted' => 'Dikonversi',
                    ])
                    ->default('draft')
                    ->required(),
                Forms\Components\Select::make('created_by')
                    ->label('Dibuat Oleh')
                    ->relationship('createdBy', 'name')
                    ->searchable()
                    ->preload(),
            ])->columns(2),

            Schemas\Components\Section::make('Periode & Harga')->schema([
                Forms\Components\DatePicker::make('start_date')
                    ->label('Tanggal Mulai')
                    ->required(),
                Forms\Components\DatePicker::make('end_date')
                    ->label('Tanggal Akhir')
                    ->required(),
                Forms\Components\TextInput::make('rental_type')
                    ->label('Tipe Sewa')
                    ->maxLength(50),
                Forms\Components\TextInput::make('duration_days')
                    ->label('Durasi (Hari)')
                    ->numeric(),
                Forms\Components\TextInput::make('daily_rate')
                    ->label('Tarif/Hari')
                    ->numeric()
                    ->prefix('Rp'),
                Forms\Components\TextInput::make('subtotal')
                    ->label('Subtotal')
                    ->numeric()
                    ->prefix('Rp'),
                Forms\Components\TextInput::make('addon_total')
                    ->label('Total Addon')
                    ->numeric()
                    ->prefix('Rp'),
                Forms\Components\TextInput::make('discount_amount')
                    ->label('Diskon')
                    ->numeric()
                    ->prefix('Rp'),
                Forms\Components\TextInput::make('tax_amount')
                    ->label('Pajak')
                    ->numeric()
                    ->prefix('Rp'),
                Forms\Components\TextInput::make('total_amount')
                    ->label('Total')
                    ->numeric()
                    ->prefix('Rp')
                    ->required(),
                Forms\Components\TextInput::make('deposit_amount')
                    ->label('Deposit')
                    ->numeric()
                    ->prefix('Rp'),
            ])->columns(3),

            Schemas\Components\Section::make('Validitas & Konversi')->schema([
                Forms\Components\DatePicker::make('valid_until')
                    ->label('Berlaku Hingga'),
                Forms\Components\DateTimePicker::make('sent_at')
                    ->label('Waktu Kirim'),
                Forms\Components\DateTimePicker::make('viewed_at')
                    ->label('Waktu Dilihat'),
                Forms\Components\DateTimePicker::make('accepted_at')
                    ->label('Waktu Diterima'),
                Forms\Components\DateTimePicker::make('rejected_at')
                    ->label('Waktu Ditolak'),
                Forms\Components\Select::make('converted_to_booking_id')
                    ->label('Booking Tujuan')
                    ->relationship('convertedBooking', 'booking_number')
                    ->searchable()
                    ->preload(),
            ])->columns(2),

            Schemas\Components\Section::make('Catatan')->schema([
                Forms\Components\Textarea::make('notes')
                    ->label('Catatan')
                    ->rows(2),
                Forms\Components\Textarea::make('terms_conditions')
                    ->label('Syarat & Ketentuan')
                    ->rows(3),
                Forms\Components\TextInput::make('lost_reason')
                    ->label('Alasan Gagal')
                    ->maxLength(255),
            ])->columns(1),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('quotation_number')
                    ->label('No. Kuotasi')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable(),
                Tables\Columns\TextColumn::make('vehicle.name')
                    ->label('Kendaraan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Mulai')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('valid_until')
                    ->label('Berlaku Hingga')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'sent' => 'info',
                        'viewed' => 'info',
                        'accepted' => 'success',
                        'rejected' => 'danger',
                        'expired' => 'warning',
                        'converted' => 'success',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Draft',
                        'sent' => 'Terkirim',
                        'viewed' => 'Dilihat',
                        'accepted' => 'Diterima',
                        'rejected' => 'Ditolak',
                        'expired' => 'Kedaluwarsa',
                        'converted' => 'Dikonversi',
                    ]),
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
            'index' => Pages\ListQuotations::route('/'),
            'create' => Pages\CreateQuotation::route('/create'),
            'edit' => Pages\EditQuotation::route('/{record}/edit'),
            'view' => Pages\ViewQuotation::route('/{record}'),
        ];
    }
}

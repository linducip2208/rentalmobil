<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContractResource\Pages;
use App\Models\Contract;
use Filament\Forms;
use Filament\Schemas;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;
use BackedEnum;

class ContractResource extends Resource
{
    protected static ?string $model = Contract::class;

    protected static BackedEnum | string | null $navigationIcon = 'heroicon-o-document-text';

    protected static string | UnitEnum | null $navigationGroup = '📋 Penjualan';

    protected static ?int $navigationSort = 14;

    protected static ?string $navigationLabel = 'Kontrak';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Schemas\Components\Section::make('Informasi Kontrak')->schema([
                Forms\Components\TextInput::make('contract_number')
                    ->label('No. Kontrak')
                    ->disabled()
                    ->dehydrated(false),
                Forms\Components\Select::make('rental_order_id')
                    ->label('Rental Order')
                    ->relationship('rentalOrder', 'order_number')
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('booking_id')
                    ->label('Booking')
                    ->relationship('booking', 'booking_number')
                    ->searchable()
                    ->preload(),
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
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Draft',
                        'active' => 'Aktif',
                        'signed' => 'Ditandatangani',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                    ])
                    ->default('draft')
                    ->required(),
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
                Forms\Components\TextInput::make('daily_rate')
                    ->label('Tarif/Hari')
                    ->numeric()
                    ->prefix('Rp')
                    ->required(),
                Forms\Components\TextInput::make('total_amount')
                    ->label('Total')
                    ->numeric()
                    ->prefix('Rp')
                    ->required(),
                Forms\Components\TextInput::make('deposit_amount')
                    ->label('Deposit')
                    ->numeric()
                    ->prefix('Rp'),
                Forms\Components\TextInput::make('km_limit')
                    ->label('Batas KM')
                    ->numeric()
                    ->suffix('km'),
                Forms\Components\TextInput::make('version')
                    ->label('Versi')
                    ->numeric()
                    ->default(1),
            ])->columns(2),

            Schemas\Components\Section::make('Kebijakan')->schema([
                Forms\Components\TextInput::make('fuel_policy')
                    ->label('Kebijakan Bahan Bakar')
                    ->maxLength(100),
                Forms\Components\TextInput::make('usage_area')
                    ->label('Area Penggunaan')
                    ->maxLength(255),
                Forms\Components\Textarea::make('late_policy')
                    ->label('Kebijakan Keterlambatan')
                    ->rows(2),
                Forms\Components\Textarea::make('damage_policy')
                    ->label('Kebijakan Kerusakan')
                    ->rows(2),
                Forms\Components\Textarea::make('accident_policy')
                    ->label('Kebijakan Kecelakaan')
                    ->rows(2),
                Forms\Components\Textarea::make('loss_policy')
                    ->label('Kebijakan Kehilangan')
                    ->rows(2),
                Forms\Components\TextInput::make('insurance_policy')
                    ->label('Asuransi')
                    ->maxLength(255),
            ])->columns(2),

            Schemas\Components\Section::make('Tanda Tangan & Catatan')->schema([
                Forms\Components\TextInput::make('customer_signature_url')
                    ->label('TTD Customer')
                    ->maxLength(500),
                Forms\Components\TextInput::make('staff_signature_url')
                    ->label('TTD Staff')
                    ->maxLength(500),
                Forms\Components\DateTimePicker::make('signed_at')
                    ->label('Waktu Tanda Tangan'),
                Forms\Components\TextInput::make('document_hash')
                    ->label('Document Hash')
                    ->maxLength(255),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('contract_number')
                    ->label('No. Kontrak')
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
                Tables\Columns\TextColumn::make('end_date')
                    ->label('Selesai')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'active' => 'info',
                        'signed' => 'success',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Draft',
                        'active' => 'Aktif',
                        'signed' => 'Ditandatangani',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
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
            'index' => Pages\ListContracts::route('/'),
            'create' => Pages\CreateContract::route('/create'),
            'edit' => Pages\EditContract::route('/{record}/edit'),
            'view' => Pages\ViewContract::route('/{record}'),
        ];
    }
}

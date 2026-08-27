<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContractResource\Pages;
use App\Filament\Resources\EnterpriseResource as Resource;
use App\Models\Contract;
use App\Services\HandoverLinkService;
use BackedEnum;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Schemas;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class ContractResource extends Resource
{
    protected static ?string $model = Contract::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-document-text';

    protected static string|UnitEnum|null $navigationGroup = 'Rental';

    protected static ?int $navigationSort = 6;

    protected static ?string $navigationLabel = 'Kontrak';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Schemas\Components\Section::make('Informasi Kontrak')->schema([
                TextInput::make('contract_number')
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
                TextInput::make('rental_type')
                    ->label('Tipe Sewa')
                    ->maxLength(50),
                TextInput::make('daily_rate')
                    ->label('Tarif/Hari')
                    ->numeric()
                    ->prefix('Rp')
                    ->required(),
                TextInput::make('total_amount')
                    ->label('Total')
                    ->numeric()
                    ->prefix('Rp')
                    ->required(),
                TextInput::make('deposit_amount')
                    ->label('Deposit')
                    ->numeric()
                    ->prefix('Rp'),
                TextInput::make('km_limit')
                    ->label('Batas KM')
                    ->numeric()
                    ->suffix('km'),
                TextInput::make('version')
                    ->label('Versi')
                    ->numeric()
                    ->default(1),
            ])->columns(2),

            Schemas\Components\Section::make('Kebijakan')->schema([
                TextInput::make('fuel_policy')
                    ->label('Kebijakan Bahan Bakar')
                    ->maxLength(100),
                TextInput::make('usage_area')
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
                TextInput::make('insurance_policy')
                    ->label('Asuransi')
                    ->maxLength(255),
            ])->columns(2),

            Schemas\Components\Section::make('Tanda Tangan & Catatan')->schema([
                TextInput::make('customer_signature_url')
                    ->label('TTD Customer')
                    ->maxLength(500),
                TextInput::make('staff_signature_url')
                    ->label('TTD Staff')
                    ->maxLength(500),
                Forms\Components\DateTimePicker::make('signed_at')
                    ->label('Waktu Tanda Tangan'),
                TextInput::make('document_hash')
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
                Actions\Action::make('signLink')
                    ->label('Link TTE')
                    ->icon('heroicon-o-pencil-square')
                    ->color('info')
                    ->visible(fn ($record) => ! $record->isSigned())
                    ->modalHeading('Link Tanda Tangan Elektronik')
                    ->modalDescription('Bagikan link ini ke penyewa via WhatsApp/email. Link ber-hash aman & kedaluwarsa otomatis.')
                    ->mountUsing(function (Schema $form, $record) {
                        $link = app(HandoverLinkService::class)->issueContractSigning($record);
                        $form->fill(['link' => $link]);
                    })
                    ->form([
                        TextInput::make('link')
                            ->label('URL tanda tangan')
                            ->readOnly()
                            ->copyable()
                            ->columnSpanFull(),
                        ViewField::make('qr')
                            ->view('filament.contracts.qr-link')
                            ->columnSpanFull(),
                    ])
                    ->action(fn () => null)
                    ->modalSubmitActionLabel('Selesai'),
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
            'index' => Pages\ListContracts::route('/'),
            'create' => Pages\CreateContract::route('/create'),
            'edit' => Pages\EditContract::route('/{record}/edit'),
            'view' => Pages\ViewContract::route('/{record}'),
        ];
    }
}

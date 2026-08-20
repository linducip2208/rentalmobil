<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Models\Payment;
use Filament\Forms;
use Filament\Schemas;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Actions;
use Filament\Tables\Table;
use UnitEnum;
use BackedEnum;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static BackedEnum | string | null $navigationIcon = 'heroicon-o-banknotes';

    protected static string | UnitEnum | null $navigationGroup = '📋 Penjualan';

    protected static ?int $navigationSort = 14;

    protected static ?string $navigationLabel = 'Pembayaran';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Schemas\Components\Section::make('Informasi Pembayaran')->schema([
                Forms\Components\Select::make('invoice_id')
                    ->label('Invoice')
                    ->relationship('invoice', 'invoice_number')
                    ->searchable()
                    ->preload()
                    ->placeholder('— Tanpa invoice —'),
                Forms\Components\Select::make('rental_order_id')
                    ->label('Order Sewa')
                    ->relationship('rentalOrder', 'order_number')
                    ->searchable()
                    ->preload()
                    ->placeholder('— Tanpa order —'),
                Forms\Components\Select::make('customer_id')
                    ->label('Customer')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('payment_method_id')
                    ->label('Metode Bayar')
                    ->relationship('paymentMethod', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make('amount')
                    ->label('Jumlah (Rp)')
                    ->numeric()
                    ->required()
                    ->prefix('Rp'),
                Forms\Components\DatePicker::make('payment_date')
                    ->label('Tanggal Bayar')
                    ->required(),
                Forms\Components\TextInput::make('reference_number')
                    ->label('No. Referensi')
                    ->maxLength(100),
                Forms\Components\FileUpload::make('proof_path')
                    ->label('Bukti Bayar')
                    ->disk('public')
                    ->directory('payments')
                    ->maxSize(5120),
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Menunggu',
                        'verified' => 'Terverifikasi',
                        'rejected' => 'Ditolak',
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
                Tables\Columns\TextColumn::make('payment_number')
                    ->label('No. Bayar')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Jumlah')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'verified' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Menunggu',
                        'verified' => 'Terverifikasi',
                        'rejected' => 'Ditolak',
                        default => ucfirst($state),
                    }),
                Tables\Columns\TextColumn::make('verified_at')
                    ->label('Diverifikasi')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Menunggu',
                        'verified' => 'Terverifikasi',
                        'rejected' => 'Ditolak',
                    ]),
                Tables\Filters\Filter::make('payment_date')
                    ->label('Tanggal')
                    ->modalForm([
                        Forms\Components\DatePicker::make('date_from')->label('Dari'),
                        Forms\Components\DatePicker::make('date_until')->label('Sampai'),
                    ])
                    ->query(function ($query, array $data): void {
                        $query->when($data['date_from'], fn ($q, $date) => $q->where('payment_date', '>=', $date));
                        $query->when($data['date_until'], fn ($q, $date) => $q->where('payment_date', '<=', $date));
                    }),
            ])
            ->actions([
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
            'index' => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
            'view' => Pages\ViewPayment::route('/{record}'),
        ];
    }
}

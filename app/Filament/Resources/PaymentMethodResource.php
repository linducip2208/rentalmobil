<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentMethodResource\Pages;
use App\Models\PaymentMethod;
use Filament\Forms;
use Filament\Schemas;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Actions;
use Filament\Tables\Table;
use UnitEnum;
use BackedEnum;

class PaymentMethodResource extends Resource
{
    protected static ?string $model = PaymentMethod::class;

    protected static BackedEnum | string | null $navigationIcon = 'heroicon-o-credit-card';

    protected static string | UnitEnum | null $navigationGroup = '🗂️ Data Utama';

    protected static ?int $navigationSort = 8;

    protected static ?string $navigationLabel = 'Metode Bayar';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Schemas\Components\Section::make('Informasi Metode Bayar')->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('type')
                    ->label('Tipe')
                    ->options([
                        'bank_transfer' => 'Transfer Bank',
                        'ewallet' => 'E-Wallet',
                        'cash' => 'Tunai',
                        'credit_card' => 'Kartu Kredit',
                        'debit_card' => 'Kartu Debit',
                        'qris' => 'QRIS',
                        'other' => 'Lainnya',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('icon')
                    ->label('Icon')
                    ->maxLength(100),
                Forms\Components\TextInput::make('bank_name')
                    ->label('Nama Bank')
                    ->maxLength(100),
                Forms\Components\TextInput::make('account_name')
                    ->label('Nama Rekening')
                    ->maxLength(255),
                Forms\Components\TextInput::make('account_number')
                    ->label('No. Rekening')
                    ->maxLength(50),
                Forms\Components\Textarea::make('instructions')
                    ->label('Instruksi Pembayaran')
                    ->rows(3),
                Forms\Components\Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
                Forms\Components\TextInput::make('sort_order')
                    ->label('Urutan')
                    ->numeric()
                    ->default(0),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'bank_transfer' => 'primary',
                        'ewallet' => 'success',
                        'cash' => 'warning',
                        'credit_card' => 'info',
                        'debit_card' => 'info',
                        'qris' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'bank_transfer' => 'Transfer Bank',
                        'ewallet' => 'E-Wallet',
                        'cash' => 'Tunai',
                        'credit_card' => 'Kartu Kredit',
                        'debit_card' => 'Kartu Debit',
                        'qris' => 'QRIS',
                        default => ucfirst($state),
                    }),
                Tables\Columns\TextColumn::make('bank_name')
                    ->label('Bank')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('Aktif'),
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
            'index' => Pages\ListPaymentMethods::route('/'),
            'create' => Pages\CreatePaymentMethod::route('/create'),
            'edit' => Pages\EditPaymentMethod::route('/{record}/edit'),
            'view' => Pages\ViewPaymentMethod::route('/{record}'),
        ];
    }
}

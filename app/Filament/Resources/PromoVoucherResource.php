<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PromoVoucherResource\Pages;
use App\Models\PromoVoucher;
use Filament\Forms;
use Filament\Schemas;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;
use BackedEnum;

class PromoVoucherResource extends Resource
{
    protected static ?string $model = PromoVoucher::class;

    protected static BackedEnum | string | null $navigationIcon = 'heroicon-o-ticket';

    protected static string | UnitEnum | null $navigationGroup = '📋 Penjualan';

    protected static ?int $navigationSort = 17;

    protected static ?string $navigationLabel = 'Promo & Voucher';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Schemas\Components\Section::make('Informasi Promo')->schema([
                Forms\Components\TextInput::make('code')
                    ->label('Kode Voucher')
                    ->required()
                    ->maxLength(50)
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('name')
                    ->label('Nama Promo')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->label('Deskripsi')
                    ->rows(3)
                    ->maxLength(1000),
                Forms\Components\Select::make('type')
                    ->label('Tipe Diskon')
                    ->options([
                        'percentage' => 'Persen (%)',
                        'fixed' => 'Nominal (Rp)',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('value')
                    ->label('Nilai Diskon')
                    ->numeric()
                    ->required(),
                Forms\Components\TextInput::make('minimum_amount')
                    ->label('Minimal Sewa (Rp)')
                    ->numeric()
                    ->prefix('Rp'),
                Forms\Components\TextInput::make('maximum_discount')
                    ->label('Maksimal Diskon (Rp)')
                    ->numeric()
                    ->prefix('Rp'),
            ])->columns(2),

            Schemas\Components\Section::make('Batas Waktu & Penggunaan')->schema([
                Forms\Components\TextInput::make('usage_limit')
                    ->label('Batas Penggunaan')
                    ->numeric(),
                Forms\Components\TextInput::make('used_count')
                    ->label('Sudah Digunakan')
                    ->numeric()
                    ->default(0)
                    ->disabled(),
                Forms\Components\DatePicker::make('start_date')
                    ->label('Tanggal Mulai')
                    ->required(),
                Forms\Components\DatePicker::make('end_date')
                    ->label('Tanggal Selesai')
                    ->required(),
                Forms\Components\Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Kode')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'percentage' => 'info',
                        'fixed' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'percentage' => 'Persen',
                        'fixed' => 'Nominal',
                        default => ucfirst($state),
                    }),
                Tables\Columns\TextColumn::make('value')
                    ->label('Nilai')
                    ->sortable(),
                Tables\Columns\TextColumn::make('usage_limit')
                    ->label('Penggunaan')
                    ->formatStateUsing(fn ($state, PromoVoucher $record): string => "{$record->used_count}/" . ($state ?? '∞')),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Mulai')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('Selesai')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('Aktif'),
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
            'index' => Pages\ListPromoVouchers::route('/'),
            'create' => Pages\CreatePromoVoucher::route('/create'),
            'edit' => Pages\EditPromoVoucher::route('/{record}/edit'),
            'view' => Pages\ViewPromoVoucher::route('/{record}'),
        ];
    }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReturnRecordResource\Pages;
use App\Models\ReturnRecord;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ReturnRecordResource extends Resource
{
    protected static ?string $model = ReturnRecord::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-uturn-left';

    protected static ?string $navigationGroup = '📋 Penjualan';

    protected static ?int $navigationSort = 15;

    protected static ?string $navigationLabel = 'Pengembalian';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informasi Pengembalian')->schema([
                Forms\Components\Select::make('rental_order_id')
                    ->label('Order Sewa')
                    ->relationship('rentalOrder', 'order_number')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\DateTimePicker::make('return_date')
                    ->label('Tanggal Kembali')
                    ->required(),
                Forms\Components\TextInput::make('return_km')
                    ->label('KM Saat Kembali')
                    ->numeric(),
                Forms\Components\TextInput::make('fuel_level')
                    ->label('Level Bensin')
                    ->maxLength(20),
            ])->columns(2),

            Forms\Components\Section::make('Kondisi Kendaraan')->schema([
                Forms\Components\Select::make('body_condition')
                    ->label('Kondisi Body')
                    ->options([
                        'excellent' => 'Sangat Baik',
                        'good' => 'Baik',
                        'fair' => 'Cukup',
                        'poor' => 'Buruk',
                    ]),
                Forms\Components\Select::make('interior_condition')
                    ->label('Kondisi Interior')
                    ->options([
                        'excellent' => 'Sangat Baik',
                        'good' => 'Baik',
                        'fair' => 'Cukup',
                        'poor' => 'Buruk',
                    ]),
                Forms\Components\Select::make('tire_condition')
                    ->label('Kondisi Ban')
                    ->options([
                        'excellent' => 'Sangat Baik',
                        'good' => 'Baik',
                        'fair' => 'Cukup',
                        'poor' => 'Buruk',
                    ]),
                Forms\Components\Textarea::make('condition_notes')
                    ->label('Catatan Kondisi')
                    ->rows(2),
                Forms\Components\Toggle::make('has_damage')
                    ->label('Ada Kerusakan')
                    ->default(false),
                Forms\Components\Textarea::make('damage_description')
                    ->label('Deskripsi Kerusakan')
                    ->rows(2),
            ])->columns(2),

            Forms\Components\Section::make('Biaya Tambahan')->schema([
                Forms\Components\TextInput::make('extra_charge')
                    ->label('Biaya Tambahan (Rp)')
                    ->numeric()
                    ->prefix('Rp')
                    ->default(0),
                Forms\Components\TextInput::make('late_minutes')
                    ->label('Keterlambatan (Menit)')
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('late_fee')
                    ->label('Denda Keterlambatan (Rp)')
                    ->numeric()
                    ->prefix('Rp')
                    ->default(0),
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Menunggu',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                    ])
                    ->default('pending')
                    ->required(),
                Forms\Components\Textarea::make('rejection_reason')
                    ->label('Alasan Penolakan')
                    ->rows(2),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('rentalOrder.order_number')
                    ->label('No. Order')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('rentalOrder.vehicle.name')
                    ->label('Kendaraan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('return_date')
                    ->label('Tanggal Kembali')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                Tables\Columns\IconColumn::make('has_damage')
                    ->label('Kerusakan')
                    ->boolean(),
                Tables\Columns\TextColumn::make('extra_charge')
                    ->label('Biaya Tambahan')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('late_fee')
                    ->label('Denda')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Menunggu',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        default => ucfirst($state),
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Menunggu',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
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
            'index' => Pages\ListReturnRecords::route('/'),
            'create' => Pages\CreateReturnRecord::route('/create'),
            'edit' => Pages\EditReturnRecord::route('/{record}/edit'),
            'view' => Pages\ViewReturnRecord::route('/{record}'),
        ];
    }
}

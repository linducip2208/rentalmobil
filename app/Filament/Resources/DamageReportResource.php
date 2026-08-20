<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DamageReportResource\Pages;
use App\Models\DamageReport;
use Filament\Forms;
use Filament\Schemas;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;
use BackedEnum;

class DamageReportResource extends Resource
{
    protected static ?string $model = DamageReport::class;

    protected static BackedEnum | string | null $navigationIcon = 'heroicon-o-exclamation-triangle';

    protected static string | UnitEnum | null $navigationGroup = '📋 Penjualan';

    protected static ?int $navigationSort = 16;

    protected static ?string $navigationLabel = 'Laporan Kerusakan';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Schemas\Components\Section::make('Informasi Kerusakan')->schema([
                Forms\Components\Select::make('vehicle_id')
                    ->label('Kendaraan')
                    ->relationship('vehicle', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('rental_order_id')
                    ->label('Order Sewa')
                    ->relationship('rentalOrder', 'order_number')
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('customer_id')
                    ->label('Customer')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('return_record_id')
                    ->label('Pengembalian')
                    ->relationship('returnRecord', 'return_date')
                    ->searchable()
                    ->preload()
                    ->placeholder('— Tidak terkait —'),
            ])->columns(2),

            Schemas\Components\Section::make('Detail Kerusakan')->schema([
                Forms\Components\Select::make('damage_type')
                    ->label('Jenis Kerusakan')
                    ->options([
                        'body' => 'Body',
                        'interior' => 'Interior',
                        'mechanical' => 'Mesin',
                        'electrical' => 'Kelistrikan',
                        'tire' => 'Ban',
                        'glass' => 'Kaca',
                        'paint' => 'Cat',
                        'other' => 'Lainnya',
                    ])
                    ->required(),
                Forms\Components\Select::make('damage_location')
                    ->label('Lokasi Kerusakan')
                    ->options([
                        'front' => 'Depan',
                        'rear' => 'Belakang',
                        'left' => 'Kiri',
                        'right' => 'Kanan',
                        'top' => 'Atas',
                        'bottom' => 'Bawah',
                        'inside' => 'Dalam',
                        'other' => 'Lainnya',
                    ]),
                Forms\Components\Select::make('severity')
                    ->label('Tingkat Kerusakan')
                    ->options([
                        'minor' => 'Ringan',
                        'moderate' => 'Sedang',
                        'severe' => 'Berat',
                        'critical' => 'Sangat Berat',
                    ])
                    ->required(),
                Forms\Components\Textarea::make('description')
                    ->label('Deskripsi')
                    ->rows(3)
                    ->required(),
                Forms\Components\TextInput::make('estimated_cost')
                    ->label('Estimasi Biaya (Rp)')
                    ->numeric()
                    ->prefix('Rp'),
                Forms\Components\TextInput::make('actual_cost')
                    ->label('Biaya Aktual (Rp)')
                    ->numeric()
                    ->prefix('Rp'),
            ])->columns(2),

            Schemas\Components\Section::make('Status Penilaian')->schema([
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Menunggu',
                        'assessed' => 'Dinilai',
                        'repaired' => 'Diperbaiki',
                        'charged' => 'Ditagihkan',
                        'write_off' => 'Ditulis Off',
                    ])
                    ->default('pending')
                    ->required(),
                Forms\Components\Textarea::make('assessment_notes')
                    ->label('Catatan Penilaian')
                    ->rows(2),
                Forms\Components\Toggle::make('charged_to_customer')
                    ->label('Ditagihkan ke Customer')
                    ->default(false),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('vehicle.name')
                    ->label('Kendaraan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('damage_type')
                    ->label('Jenis')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'body' => 'warning',
                        'interior' => 'info',
                        'mechanical' => 'danger',
                        'electrical' => 'info',
                        'tire' => 'warning',
                        'glass' => 'info',
                        'paint' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'body' => 'Body',
                        'interior' => 'Interior',
                        'mechanical' => 'Mesin',
                        'electrical' => 'Kelistrikan',
                        'tire' => 'Ban',
                        'glass' => 'Kaca',
                        'paint' => 'Cat',
                        default => ucfirst($state),
                    }),
                Tables\Columns\TextColumn::make('severity')
                    ->label('Tingkat')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'minor' => 'success',
                        'moderate' => 'warning',
                        'severe' => 'danger',
                        'critical' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'minor' => 'Ringan',
                        'moderate' => 'Sedang',
                        'severe' => 'Berat',
                        'critical' => 'Sangat Berat',
                        default => ucfirst($state),
                    }),
                Tables\Columns\TextColumn::make('estimated_cost')
                    ->label('Estimasi')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('actual_cost')
                    ->label('Aktual')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'assessed' => 'info',
                        'repaired' => 'success',
                        'charged' => 'primary',
                        'write_off' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Menunggu',
                        'assessed' => 'Dinilai',
                        'repaired' => 'Diperbaiki',
                        'charged' => 'Ditagihkan',
                        'write_off' => 'Ditulis Off',
                        default => ucfirst($state),
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Menunggu',
                        'assessed' => 'Dinilai',
                        'repaired' => 'Diperbaiki',
                        'charged' => 'Ditagihkan',
                        'write_off' => 'Ditulis Off',
                    ]),
                Tables\Filters\SelectFilter::make('severity')
                    ->label('Tingkat')
                    ->options([
                        'minor' => 'Ringan',
                        'moderate' => 'Sedang',
                        'severe' => 'Berat',
                        'critical' => 'Sangat Berat',
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
            'index' => Pages\ListDamageReports::route('/'),
            'create' => Pages\CreateDamageReport::route('/create'),
            'edit' => Pages\EditDamageReport::route('/{record}/edit'),
            'view' => Pages\ViewDamageReport::route('/{record}'),
        ];
    }
}

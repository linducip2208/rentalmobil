<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvestigationCaseResource\Pages;
use App\Models\InvestigationCase;
use Filament\Forms;
use Filament\Schemas;
use Filament\Schemas\Schema;
use App\Filament\Resources\EnterpriseResource as Resource;
use Filament\Tables;
use Filament\Actions;
use Filament\Tables\Table;
use UnitEnum;
use BackedEnum;

class InvestigationCaseResource extends Resource
{
    protected static ?string $model = InvestigationCase::class;

    protected static BackedEnum | string | null $navigationIcon = 'heroicon-o-magnifying-glass';

    protected static string | UnitEnum | null $navigationGroup = 'Risk & Security';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Kasus Investigasi';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Schemas\Components\Section::make('Informasi Kasus')->schema([
                Forms\Components\TextInput::make('title')
                    ->label('Judul')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->label('Deskripsi')
                    ->rows(3)
                    ->required(),
                Forms\Components\Select::make('vehicle_id')
                    ->label('Kendaraan')
                    ->relationship('vehicle', 'name')
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('customer_id')
                    ->label('Customer')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('rental_order_id')
                    ->label('Order Sewa')
                    ->relationship('rentalOrder', 'order_number')
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('assigned_to')
                    ->label('Ditugaskan Ke')
                    ->relationship('assignedTo', 'name')
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('type')
                    ->label('Jenis Kasus')
                    ->options([
                        'missing_vehicle' => 'Kendaraan Hilang',
                        'damage_dispute' => 'Sengketa Kerusakan',
                        'payment_fraud' => 'Penipuan Pembayaran',
                        'document_fraud' => 'Pemalsuan Dokumen',
                        'accident' => 'Kecelakaan',
                        'theft' => 'Pencurian',
                        'other' => 'Lainnya',
                    ])
                    ->required(),
                Forms\Components\Select::make('priority')
                    ->label('Prioritas')
                    ->options([
                        'low' => 'Rendah',
                        'medium' => 'Sedang',
                        'high' => 'Tinggi',
                        'urgent' => 'Mendesak',
                    ])
                    ->default('medium')
                    ->required(),
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'open' => 'Terbuka',
                        'in_progress' => 'Dalam Investigasi',
                        'resolved' => 'Teratasi',
                        'closed' => 'Ditutup',
                    ])
                    ->default('open')
                    ->required(),
            ])->columns(2),

            Schemas\Components\Section::make('Hasil Investigasi')->schema([
                Forms\Components\Textarea::make('findings')
                    ->label('Temuan')
                    ->rows(3),
                Forms\Components\Textarea::make('resolution')
                    ->label('Resolusi')
                    ->rows(3),
                Forms\Components\DateTimePicker::make('opened_at')
                    ->label('Tanggal Dibuka'),
                Forms\Components\DateTimePicker::make('resolved_at')
                    ->label('Tanggal Diselesaikan'),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('case_number')
                    ->label('No. Kasus')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->limit(40),
                Tables\Columns\TextColumn::make('vehicle.name')
                    ->label('Kendaraan')
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'open' => 'warning',
                        'in_progress' => 'info',
                        'resolved' => 'success',
                        'closed' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'open' => 'Terbuka',
                        'in_progress' => 'Dalam Investigasi',
                        'resolved' => 'Teratasi',
                        'closed' => 'Ditutup',
                        default => ucfirst($state),
                    }),
                Tables\Columns\TextColumn::make('priority')
                    ->label('Prioritas')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'low' => 'success',
                        'medium' => 'warning',
                        'high' => 'danger',
                        'urgent' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'low' => 'Rendah',
                        'medium' => 'Sedang',
                        'high' => 'Tinggi',
                        'urgent' => 'Mendesak',
                        default => ucfirst($state),
                    }),
                Tables\Columns\TextColumn::make('assignedTo.name')
                    ->label('Ditugaskan')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'open' => 'Terbuka',
                        'in_progress' => 'Dalam Investigasi',
                        'resolved' => 'Teratasi',
                        'closed' => 'Ditutup',
                    ]),
                Tables\Filters\SelectFilter::make('priority')
                    ->label('Prioritas')
                    ->options([
                        'low' => 'Rendah',
                        'medium' => 'Sedang',
                        'high' => 'Tinggi',
                        'urgent' => 'Mendesak',
                    ]),
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
            'index' => Pages\ListInvestigationCases::route('/'),
            'create' => Pages\CreateInvestigationCase::route('/create'),
            'edit' => Pages\EditInvestigationCase::route('/{record}/edit'),
            'view' => Pages\ViewInvestigationCase::route('/{record}'),
        ];
    }
}

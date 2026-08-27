<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VehicleResource\Pages;
use App\Models\Vehicle;
use Filament\Forms;
use Filament\Schemas;
use Filament\Schemas\Schema;
use Filament\Schemas\Get;
use App\Filament\Resources\EnterpriseResource as Resource;
use Filament\Tables;
use Filament\Actions;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use UnitEnum;
use BackedEnum;

class VehicleResource extends Resource
{
    protected static ?string $model = Vehicle::class;

    protected static BackedEnum | string | null $navigationIcon = 'heroicon-o-truck';

    protected static string | UnitEnum | null $navigationGroup = 'Fleet';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Kendaraan';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Schemas\Components\Section::make('Informasi Dasar')->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Get $set, ?string $state) => $set('slug', Str::slug($state ?? ''))),
                Forms\Components\TextInput::make('slug')
                    ->label('Slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Forms\Components\Select::make('category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('brand_id')
                    ->label('Merek')
                    ->relationship('brand', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('location_id')
                    ->label('Lokasi')
                    ->relationship('location', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Textarea::make('description')
                    ->label('Deskripsi')
                    ->rows(3)
                    ->maxLength(1000),
            ])->columns(2),

            Schemas\Components\Section::make('Detail Kendaraan')->schema([
                Forms\Components\TextInput::make('license_plate')
                    ->label('No. Polisi')
                    ->required()
                    ->maxLength(20)
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('year')
                    ->label('Tahun')
                    ->numeric()
                    ->required()
                    ->minValue(1990)
                    ->maxValue(date('Y') + 1),
                Forms\Components\TextInput::make('color')
                    ->label('Warna')
                    ->maxLength(50),
                Forms\Components\TextInput::make('seats')
                    ->label('Jumlah Kursi')
                    ->numeric()
                    ->default(4),
                Forms\Components\TextInput::make('engine_cc')
                    ->label('Kapasitas Mesin (cc)')
                    ->numeric(),
                Forms\Components\Select::make('transmission')
                    ->label('Transmisi')
                    ->options([
                        'manual' => 'Manual',
                        'automatic' => 'Automatic',
                        'cvt' => 'CVT',
                    ])
                    ->required(),
                Forms\Components\Select::make('fuel_type')
                    ->label('Jenis BBM')
                    ->options([
                        'gasoline' => 'Bensin',
                        'diesel' => 'Diesel',
                        'electric' => 'Listrik',
                        'hybrid' => 'Hybrid',
                        'lpg' => 'LPG',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('current_km')
                    ->label('Kilometer Saat Ini')
                    ->numeric()
                    ->default(0),
                Forms\Components\Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
                Forms\Components\Toggle::make('is_insured')
                    ->label('Asuransi Aktif')
                    ->default(false),
            ])->columns(3),

            Schemas\Components\Section::make('Harga Sewa')->schema([
                Forms\Components\TextInput::make('daily_rate')
                    ->label('Tarif Harian (Rp)')
                    ->numeric()
                    ->required()
                    ->prefix('Rp'),
                Forms\Components\TextInput::make('weekly_rate')
                    ->label('Tarif Mingguan (Rp)')
                    ->numeric()
                    ->prefix('Rp'),
                Forms\Components\TextInput::make('monthly_rate')
                    ->label('Tarif Bulanan (Rp)')
                    ->numeric()
                    ->prefix('Rp'),
                Forms\Components\TextInput::make('deposit_amount')
                    ->label('Deposit (Rp)')
                    ->numeric()
                    ->prefix('Rp'),
                Forms\Components\TextInput::make('late_fee_per_hour')
                    ->label('Denda Per Jam (Rp)')
                    ->numeric()
                    ->prefix('Rp'),
            ])->columns(3),

            Schemas\Components\Section::make('Foto & Fitur')->schema([
                Forms\Components\FileUpload::make('image')
                    ->label('Foto Utama')
                    ->image()
                    ->disk('public')
                    ->directory('vehicles')
                    ->maxSize(5120),
                Forms\Components\KeyValue::make('features')
                    ->label('Fitur Kendaraan')
                    ->placeholder('Tambah fitur'),
            ])->columns(2),

            Schemas\Components\Section::make('Status Servis')->schema([
                Forms\Components\DateTimePicker::make('last_serviced_at')
                    ->label('Terakhir Diservis'),
                Forms\Components\DateTimePicker::make('last_km_at')
                    ->label('Terakhir Catat KM'),
            ])->columns(2),

            Schemas\Components\Section::make('Dokumen & Pajak Kendaraan')
                ->description('Booking otomatis ditolak jika dokumen kedaluwarsa selama masa sewa. Reminder dikirim H-30 dan H-7.')
                ->schema([
                    Forms\Components\DatePicker::make('stnk_due_date')->label('Jatuh Tempo STNK')->native(false),
                    Forms\Components\DatePicker::make('tax_due_date')->label('Pajak Tahunan')->native(false),
                    Forms\Components\DatePicker::make('tax_5y_due_date')->label('Pajak 5 Tahunan')->native(false),
                    Forms\Components\DatePicker::make('kir_due_date')->label('Uji Berkala KIR')->native(false),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Foto')
                    ->disk('public')
                    ->circular(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('license_plate')
                    ->label('No. Polisi')
                    ->searchable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori')
                    ->sortable(),
                Tables\Columns\TextColumn::make('brand.name')
                    ->label('Merek')
                    ->sortable(),
                Tables\Columns\TextColumn::make('location.name')
                    ->label('Lokasi')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'available' => 'success',
                        'rented' => 'warning',
                        'maintenance' => 'danger',
                        'reserved' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'available' => 'Tersedia',
                        'rented' => 'Disewa',
                        'maintenance' => 'Servis',
                        'reserved' => 'Direservasi',
                        default => ucfirst($state),
                    }),
                Tables\Columns\TextColumn::make('daily_rate')
                    ->label('Tarif/Hari')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('current_km')
                    ->label('KM')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'available' => 'Tersedia',
                        'rented' => 'Disewa',
                        'maintenance' => 'Servis',
                        'reserved' => 'Direservasi',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Aktif'),
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name'),
                Tables\Filters\SelectFilter::make('brand_id')
                    ->label('Merek')
                    ->relationship('brand', 'name'),
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
            'index' => Pages\ListVehicles::route('/'),
            'create' => Pages\CreateVehicle::route('/create'),
            'edit' => Pages\EditVehicle::route('/{record}/edit'),
            'view' => Pages\ViewVehicle::route('/{record}'),
        ];
    }
}

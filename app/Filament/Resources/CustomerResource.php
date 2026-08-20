<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Models\Customer;
use Filament\Forms;
use Filament\Schemas;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Actions;
use Filament\Tables\Table;
use UnitEnum;
use BackedEnum;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static BackedEnum | string | null $navigationIcon = 'heroicon-o-users';

    protected static string | UnitEnum | null $navigationGroup = '🗂️ Data Utama';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationLabel = 'Pelanggan';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Schemas\Components\Section::make('Data Diri')->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('password')
                    ->label('Password portal')
                    ->password()->revealable()
                    ->dehydrated(fn (?string $state) => filled($state))
                    ->helperText('Kosongkan saat edit bila password tidak berubah.'),
                Forms\Components\TextInput::make('phone')
                    ->label('Telepon')
                    ->tel()
                    ->required()
                    ->maxLength(20),
                Forms\Components\TextInput::make('address')
                    ->label('Alamat')
                    ->maxLength(500),
                Forms\Components\TextInput::make('city')
                    ->label('Kota')
                    ->maxLength(100),
                Forms\Components\TextInput::make('province')
                    ->label('Provinsi')
                    ->maxLength(100),
                Forms\Components\TextInput::make('postal_code')
                    ->label('Kode Pos')
                    ->maxLength(10),
                Forms\Components\TextInput::make('ktp_number')
                    ->label('Nomor KTP')
                    ->maxLength(50),
                Forms\Components\TextInput::make('sim_number')->label('Nomor SIM')->maxLength(50),
                Forms\Components\Select::make('customer_type')->label('Jenis customer')->options(['individual'=>'Perorangan','corporate'=>'Perusahaan'])->default('individual'),
            ])->columns(2),

            Schemas\Components\Section::make('Perusahaan & Kontak Darurat')->schema([
                Forms\Components\TextInput::make('company_name')
                    ->label('Nama Perusahaan')
                    ->maxLength(255),
                Forms\Components\TextInput::make('company_address')
                    ->label('Alamat Perusahaan')
                    ->maxLength(500),
                Forms\Components\TextInput::make('emergency_contact_name')
                    ->label('Kontak Darurat (Nama)')
                    ->maxLength(255),
                Forms\Components\TextInput::make('emergency_contact_phone')
                    ->label('Kontak Darurat (Telp)')
                    ->maxLength(20),
            ])->columns(2),

            Schemas\Components\Section::make('Skor & Catatan')->schema([
                Forms\Components\TextInput::make('trust_score')
                    ->label('Skor Kepercayaan')
                    ->numeric()
                    ->default(50)
                    ->minValue(0)
                    ->maxValue(100),
                Forms\Components\Textarea::make('notes')
                    ->label('Catatan')
                    ->rows(2),
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
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Telepon')
                    ->searchable(),
                Tables\Columns\TextColumn::make('city')
                    ->label('Kota')
                    ->sortable(),
                Tables\Columns\TextColumn::make('trust_score')
                    ->label('Skor')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_orders')
                    ->label('Total Order')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_spent')
                    ->label('Total Belanja')
                    ->money('IDR')
                    ->sortable(),
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
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
            'view' => Pages\ViewCustomer::route('/{record}'),
        ];
    }
}

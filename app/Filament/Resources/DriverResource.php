<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DriverResource\Pages;
use App\Filament\Resources\EnterpriseResource as Resource;
use App\Models\Driver;
use BackedEnum;
use Filament\Actions;
use Filament\Forms;
use Filament\Schemas;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class DriverResource extends Resource
{
    protected static ?string $model = Driver::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-user-circle';

    protected static string|UnitEnum|null $navigationGroup = 'Fleet';

    protected static ?int $navigationSort = 7;

    protected static ?string $navigationLabel = 'Supir';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Schemas\Components\Section::make('Informasi Supir')->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('phone')
                    ->label('Telepon')
                    ->tel()
                    ->required()
                    ->maxLength(20),
                Forms\Components\TextInput::make('address')
                    ->label('Alamat')
                    ->maxLength(500),
                Forms\Components\TextInput::make('emergency_contact')
                    ->label('Kontak Darurat')
                    ->maxLength(255),
                Forms\Components\Textarea::make('notes')
                    ->label('Catatan')
                    ->rows(2),
            ])->columns(2),

            Schemas\Components\Section::make('SIM & Status')->schema([
                Forms\Components\TextInput::make('license_number')
                    ->label('No. SIM')
                    ->required()
                    ->maxLength(50),
                Forms\Components\DatePicker::make('license_expiry')
                    ->label('Masa Berlaku SIM')
                    ->required(),
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'active' => 'Aktif',
                        'inactive' => 'Tidak Aktif',
                        'on_leave' => 'Cuti',
                        'suspended' => 'Ditangguhkan',
                    ])
                    ->default('active')
                    ->required(),
                Forms\Components\TextInput::make('rating')
                    ->label('Rating')
                    ->numeric()
                    ->default(5.0)
                    ->minValue(0)
                    ->maxValue(5),
                Forms\Components\TextInput::make('total_trips')
                    ->label('Total Perjalanan')
                    ->numeric()
                    ->default(0),
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
                Tables\Columns\TextColumn::make('phone')
                    ->label('Telepon')
                    ->searchable(),
                Tables\Columns\TextColumn::make('license_number')
                    ->label('No. SIM')
                    ->searchable(),
                Tables\Columns\TextColumn::make('license_expiry')
                    ->label('Berlaku Sampai')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('rating')
                    ->label('Rating')
                    ->sortable()
                    ->icon(fn ($state) => $state >= 4 ? 'heroicon-m-star' : 'heroicon-m-star')
                    ->color(fn ($state) => $state >= 4 ? 'warning' : 'gray'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                Tables\Columns\TextColumn::make('total_trips')
                    ->label('Total Trip')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('Aktif'),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'active' => 'Aktif',
                        'inactive' => 'Tidak Aktif',
                        'on_leave' => 'Cuti',
                        'suspended' => 'Ditangguhkan',
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
            'index' => Pages\ListDrivers::route('/'),
            'create' => Pages\CreateDriver::route('/create'),
            'edit' => Pages\EditDriver::route('/{record}/edit'),
            'view' => Pages\ViewDriver::route('/{record}'),
        ];
    }
}

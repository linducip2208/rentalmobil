<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BankAccountResource\Pages;
use App\Filament\Resources\EnterpriseResource as Resource;
use App\Models\BankAccount;
use BackedEnum;
use Filament\Actions;
use Filament\Forms;
use Filament\Schemas;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class BankAccountResource extends Resource
{
    protected static ?string $model = BankAccount::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-building-library';

    protected static string|UnitEnum|null $navigationGroup = 'Finance';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationLabel = 'Rekening Bank';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Schemas\Components\Section::make('Informasi Rekening')->schema([
                Forms\Components\TextInput::make('bank_name')
                    ->label('Nama Bank')
                    ->required()
                    ->maxLength(100),
                Forms\Components\TextInput::make('account_name')
                    ->label('Nama Rekening')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('account_number')
                    ->label('No. Rekening')
                    ->required()
                    ->maxLength(50),
                Forms\Components\TextInput::make('name')
                    ->label('Nama Alias')
                    ->maxLength(100),
                Forms\Components\Textarea::make('description')
                    ->label('Deskripsi')
                    ->rows(2),
                Forms\Components\TextInput::make('balance')
                    ->label('Saldo Saat Ini (Rp)')
                    ->numeric()
                    ->prefix('Rp')
                    ->default(0),
                Forms\Components\TextInput::make('initial_balance')
                    ->label('Saldo Awal (Rp)')
                    ->numeric()
                    ->prefix('Rp')
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
                Tables\Columns\TextColumn::make('bank_name')
                    ->label('Bank')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('account_name')
                    ->label('Nama Rekening')
                    ->searchable(),
                Tables\Columns\TextColumn::make('account_number')
                    ->label('No. Rekening')
                    ->searchable(),
                Tables\Columns\TextColumn::make('balance')
                    ->label('Saldo')
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
            'index' => Pages\ListBankAccounts::route('/'),
            'create' => Pages\CreateBankAccount::route('/create'),
            'edit' => Pages\EditBankAccount::route('/{record}/edit'),
            'view' => Pages\ViewBankAccount::route('/{record}'),
        ];
    }
}

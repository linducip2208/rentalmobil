<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ChartOfAccountResource\Pages;
use App\Models\ChartOfAccount;
use Filament\Forms;
use Filament\Schemas;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Actions;
use Filament\Tables\Table;
use UnitEnum;
use BackedEnum;

class ChartOfAccountResource extends Resource
{
    protected static ?string $model = ChartOfAccount::class;

    protected static BackedEnum | string | null $navigationIcon = 'heroicon-o-table-cells';

    protected static string | UnitEnum | null $navigationGroup = '💰 Keuangan';

    protected static ?int $navigationSort = 21;

    protected static ?string $navigationLabel = 'Bagan Akun';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Schemas\Components\Section::make('Informasi Akun')->schema([
                Forms\Components\TextInput::make('code')
                    ->label('Kode Akun')
                    ->required()
                    ->maxLength(20)
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('name')
                    ->label('Nama Akun')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('type')
                    ->label('Jenis Akun')
                    ->options([
                        'asset' => 'Aset',
                        'liability' => 'Kewajiban',
                        'equity' => 'Modal',
                        'revenue' => 'Pendapatan',
                        'expense' => 'Beban',
                    ])
                    ->required(),
                Forms\Components\Select::make('normal_balance')
                    ->label('Saldo Normal')
                    ->options([
                        'debit' => 'Debit',
                        'credit' => 'Kredit',
                    ])
                    ->required(),
                Forms\Components\Select::make('parent_id')
                    ->label('Akun Induk')
                    ->relationship('parent', 'name')
                    ->searchable()
                    ->preload()
                    ->placeholder('— Akun Utama —'),
                Forms\Components\Textarea::make('description')
                    ->label('Deskripsi')
                    ->rows(2),
                Forms\Components\Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
                Forms\Components\Toggle::make('is_system')
                    ->label('Akun Sistem')
                    ->default(false)
                    ->disabled(),
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
                    ->label('Jenis')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'asset' => 'info',
                        'liability' => 'warning',
                        'equity' => 'primary',
                        'revenue' => 'success',
                        'expense' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'asset' => 'Aset',
                        'liability' => 'Kewajiban',
                        'equity' => 'Modal',
                        'revenue' => 'Pendapatan',
                        'expense' => 'Beban',
                        default => ucfirst($state),
                    }),
                Tables\Columns\TextColumn::make('parent.name')
                    ->label('Induk')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_system')
                    ->label('Sistem')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Jenis')
                    ->options([
                        'asset' => 'Aset',
                        'liability' => 'Kewajiban',
                        'equity' => 'Modal',
                        'revenue' => 'Pendapatan',
                        'expense' => 'Beban',
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
            'index' => Pages\ListChartOfAccounts::route('/'),
            'create' => Pages\CreateChartOfAccount::route('/create'),
            'edit' => Pages\EditChartOfAccount::route('/{record}/edit'),
            'view' => Pages\ViewChartOfAccount::route('/{record}'),
        ];
    }
}

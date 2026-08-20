<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TrustScoreLogResource\Pages;
use App\Models\TrustScoreLog;
use Filament\Forms;
use Filament\Schemas;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Actions;
use Filament\Tables\Table;
use UnitEnum;
use BackedEnum;

class TrustScoreLogResource extends Resource
{
    protected static ?string $model = TrustScoreLog::class;

    protected static BackedEnum | string | null $navigationIcon = 'heroicon-o-chart-bar';

    protected static string | UnitEnum | null $navigationGroup = '🛡️ Security';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Skor Kepercayaan';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Schemas\Components\Section::make('Data Perubahan Skor')->schema([
                Forms\Components\Select::make('customer_id')
                    ->label('Customer')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make('previous_score')
                    ->label('Skor Sebelumnya')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->required(),
                Forms\Components\TextInput::make('new_score')
                    ->label('Skor Baru')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->required(),
                Forms\Components\TextInput::make('change_reason')
                    ->label('Alasan Perubahan')
                    ->maxLength(255)
                    ->required(),
                Forms\Components\Select::make('changed_by')
                    ->label('Diubah Oleh')
                    ->relationship('changedBy', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable(),
                Tables\Columns\TextColumn::make('previous_score')
                    ->label('Skor Lama')
                    ->sortable(),
                Tables\Columns\TextColumn::make('new_score')
                    ->label('Skor Baru')
                    ->sortable(),
                Tables\Columns\TextColumn::make('change_amount')
                    ->label('Perubahan')
                    ->sortable()
                    ->color(fn ($state): string => $state > 0 ? 'success' : ($state < 0 ? 'danger' : 'gray')),
                Tables\Columns\TextColumn::make('change_reason')
                    ->label('Alasan')
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('changedBy.name')
                    ->label('Diubah Oleh'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
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
            'index' => Pages\ListTrustScoreLogs::route('/'),
            'create' => Pages\CreateTrustScoreLog::route('/create'),
            'edit' => Pages\EditTrustScoreLog::route('/{record}/edit'),
            'view' => Pages\ViewTrustScoreLog::route('/{record}'),
        ];
    }
}

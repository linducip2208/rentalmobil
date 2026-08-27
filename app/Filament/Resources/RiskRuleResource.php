<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EnterpriseResource as Resource;
use App\Filament\Resources\RiskRuleResource\Pages;
use App\Models\RiskRule;
use Filament\Actions;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class RiskRuleResource extends Resource
{
    protected static ?string $model = RiskRule::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-finger-print';

    protected static \UnitEnum|string|null $navigationGroup = 'Risk & Security';

    protected static ?string $navigationLabel = 'Aturan Risiko';

    protected static ?int $navigationSort = 8;

    public static function form(Schema $s): Schema
    {
        return $s->components([Forms\Components\TextInput::make('name')->required(), Forms\Components\TextInput::make('field')->helperText('Path context, contoh: booking_amount atau ip')->required(), Forms\Components\Select::make('operator')->options(array_combine(['equals', 'not_equals', 'gt', 'gte', 'lt', 'lte', 'contains', 'in'], ['Sama', 'Tidak sama', '>', '>=', '<', '<=', 'Mengandung', 'Dalam daftar']))->required(), Forms\Components\TextInput::make('comparison_value'), Forms\Components\TextInput::make('score_delta')->numeric()->default(0), Forms\Components\Select::make('action')->options(['allow' => 'Izinkan', 'review' => 'Review', 'block' => 'Blokir'])->required(), Forms\Components\TextInput::make('priority')->numeric()->default(100), Forms\Components\Toggle::make('is_active')->default(true)]);
    }

    public static function table(Table $t): Table
    {
        return $t->columns([Tables\Columns\TextColumn::make('name')->searchable(), Tables\Columns\TextColumn::make('field')->badge(), Tables\Columns\TextColumn::make('operator'), Tables\Columns\TextColumn::make('score_delta'), Tables\Columns\TextColumn::make('action')->badge(), Tables\Columns\IconColumn::make('is_active')->boolean()])->recordActions([Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListRiskRules::route('/'), 'create' => Pages\CreateRiskRule::route('/create'), 'edit' => Pages\EditRiskRule::route('/{record}/edit')];
    }
}

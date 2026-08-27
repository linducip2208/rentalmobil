<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EnterpriseResource as Resource;
use App\Filament\Resources\SeasonPeriodResource\Pages;
use App\Models\SeasonPeriod;
use Filament\Actions;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class SeasonPeriodResource extends Resource
{
    protected static ?string $model = SeasonPeriod::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static \UnitEnum|string|null $navigationGroup = 'Sales & Marketing';

    protected static ?string $navigationLabel = 'Kalender Musim';

    protected static ?int $navigationSort = 9;

    public static function form(Schema $s): Schema
    {
        return $s->components([
            Forms\Components\TextInput::make('name')->label('Nama periode')->required()->maxLength(120)->placeholder('High Season Libur Akhir Tahun'),
            Forms\Components\DatePicker::make('start_date')->label('Mulai')->required()->native(false),
            Forms\Components\DatePicker::make('end_date')->label('Selesai')->required()->native(false)->afterOrEqual('start_date'),
            Forms\Components\TextInput::make('multiplier')->numeric()->step(0.05)->minValue(0.5)->maxValue(3)->default(1.25)->required()->helperText('1.25 = harga naik 25%'),
            Forms\Components\Toggle::make('is_recurring_annual')->label('Ulang tiap tahun')->helperText('Aktifkan untuk libur nasional/musiman tahunan (abaikan tahun)'),
            Forms\Components\Select::make('location_id')->relationship('location', 'name')->searchable()->preload()->placeholder('Semua cabang'),
            Forms\Components\Toggle::make('is_active')->default(true)->required(),
        ])->columns(2);
    }

    public static function table(Table $t): Table
    {
        return $t->columns([
            Tables\Columns\TextColumn::make('name')->searchable(),
            Tables\Columns\TextColumn::make('start_date')->date('d M')->label('Mulai'),
            Tables\Columns\TextColumn::make('end_date')->date('d M')->label('Selesai'),
            Tables\Columns\TextColumn::make('multiplier')->badge()->color(fn ($state) => (float) $state > 1 ? 'danger' : 'success')->formatStateUsing(fn ($state) => 'Ã—'.$state),
            Tables\Columns\IconColumn::make('is_recurring_annual')->boolean()->label('Tahunan'),
            Tables\Columns\TextColumn::make('location.name')->placeholder('Semua cabang'),
        ])
            ->recordActions([Actions\EditAction::make(), Actions\DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSeasonPeriods::route('/'),
            'create' => Pages\CreateSeasonPeriod::route('/create'),
            'edit' => Pages\EditSeasonPeriod::route('/{record}/edit'),
        ];
    }
}

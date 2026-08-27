<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EnterpriseResource as Resource;
use App\Filament\Resources\MaintenancePredictionResource\Pages;
use App\Models\MaintenancePrediction;
use Filament\Actions;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class MaintenancePredictionResource extends Resource
{
    protected static ?string $model = MaintenancePrediction::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-sparkles';

    protected static \UnitEnum|string|null $navigationGroup = 'Maintenance';

    protected static ?string $navigationLabel = 'Prediksi Maintenance';

    protected static ?int $navigationSort = 7;

    public static function form(Schema $s): Schema
    {
        return $s->components([]);
    }

    public static function table(Table $t): Table
    {
        return $t->columns([Tables\Columns\TextColumn::make('vehicle.name'), Tables\Columns\TextColumn::make('prediction_type'), Tables\Columns\TextColumn::make('predicted_date')->date(), Tables\Columns\TextColumn::make('predicted_km')->numeric(), Tables\Columns\TextColumn::make('confidence')->suffix('%'), Tables\Columns\TextColumn::make('status')->badge()])->recordActions([Actions\Action::make('resolve')->label('Selesai')->action(fn ($r) => $r->update(['status' => 'resolved']))->visible(fn ($r) => $r->status === 'open')]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListMaintenancePredictions::route('/')];
    }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EnterpriseResource as Resource;
use App\Filament\Resources\RiskAssessmentResource\Pages;
use App\Models\RiskAssessment;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class RiskAssessmentResource extends Resource
{
    protected static ?string $model = RiskAssessment::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-shield-exclamation';

    protected static \UnitEnum|string|null $navigationGroup = 'Risk & Security';

    protected static ?string $navigationLabel = 'Hasil Penilaian Risiko';

    protected static ?int $navigationSort = 9;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('created_at')->label('Waktu')->dateTime()->sortable(),
            Tables\Columns\TextColumn::make('customer.name')->label('Pelanggan')->searchable(),
            Tables\Columns\TextColumn::make('decision')->label('Keputusan')->badge(),
            Tables\Columns\TextColumn::make('score')->label('Skor')->sortable(),
            Tables\Columns\TextColumn::make('assessable_type')->label('Objek')->formatStateUsing(fn (?string $state) => $state ? class_basename($state) : '-'),
            Tables\Columns\TextColumn::make('matched_rules')->label('Aturan cocok')->formatStateUsing(fn ($state) => count($state ?? [])),
        ])->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListRiskAssessments::route('/')];
    }
}

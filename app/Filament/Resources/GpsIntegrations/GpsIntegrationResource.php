<?php

namespace App\Filament\Resources\GpsIntegrations;

use App\Filament\Resources\GpsIntegrations\Pages\CreateGpsIntegration;
use App\Filament\Resources\GpsIntegrations\Pages\EditGpsIntegration;
use App\Filament\Resources\GpsIntegrations\Pages\ListGpsIntegrations;
use App\Filament\Resources\GpsIntegrations\Schemas\GpsIntegrationForm;
use App\Filament\Resources\GpsIntegrations\Tables\GpsIntegrationsTable;
use App\Models\GpsIntegration;
use BackedEnum;
use App\Filament\Resources\EnterpriseResource as Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class GpsIntegrationResource extends Resource
{
    protected static ?string $model = GpsIntegration::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cloud-arrow-down';
    protected static \UnitEnum|string|null $navigationGroup = 'GPS & Monitoring';
    protected static ?string $navigationLabel = 'Integrasi GPS BYOK';
    protected static ?int $navigationSort = 5;


    public static function form(Schema $schema): Schema
    {
        return GpsIntegrationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GpsIntegrationsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGpsIntegrations::route('/'),
            'create' => CreateGpsIntegration::route('/create'),
            'edit' => EditGpsIntegration::route('/{record}/edit'),
        ];
    }
}

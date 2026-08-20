<?php

namespace App\Filament\Resources\GpsIntegrations\Pages;

use App\Filament\Resources\GpsIntegrations\GpsIntegrationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListGpsIntegrations extends ListRecords
{
    protected static string $resource = GpsIntegrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources\GpsGeofenceResource\Pages;

use App\Filament\Resources\GpsGeofenceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListGpsGeofences extends ListRecords
{
    protected static string $resource = GpsGeofenceResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}

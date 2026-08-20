<?php

namespace App\Filament\Resources\GpsLogResource\Pages;

use App\Filament\Resources\GpsLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewGpsLog extends ViewRecord
{
    protected static string $resource = GpsLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}

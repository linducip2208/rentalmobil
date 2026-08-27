<?php

namespace App\Filament\Resources\MaintenanceLogResource\Pages;

use App\Filament\Resources\MaintenanceLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewMaintenanceLog extends ViewRecord
{
    protected static string $resource = MaintenanceLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}

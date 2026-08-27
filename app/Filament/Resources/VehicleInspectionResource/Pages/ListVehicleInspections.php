<?php

namespace App\Filament\Resources\VehicleInspectionResource\Pages;

use App\Filament\Resources\VehicleInspectionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListVehicleInspections extends ListRecords
{
    protected static string $resource = VehicleInspectionResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}

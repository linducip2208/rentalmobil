<?php

namespace App\Filament\Resources\VehicleInspectionResource\Pages;

use App\Filament\Resources\VehicleInspectionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateVehicleInspection extends CreateRecord
{
    protected static string $resource = VehicleInspectionResource::class;

    protected function mutateFormDataBeforeCreate(array $d): array
    {
        $d['inspector_id'] = auth()->id();

        return $d;
    }
}

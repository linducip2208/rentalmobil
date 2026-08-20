<?php

namespace App\Filament\Resources\GpsLogResource\Pages;

use App\Filament\Resources\GpsLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGpsLogs extends ListRecords
{
    protected static string $resource = GpsLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

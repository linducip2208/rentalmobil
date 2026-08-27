<?php

namespace App\Filament\Resources\GpsCommandResource\Pages;

use App\Filament\Resources\GpsCommandResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListGpsCommands extends ListRecords
{
    protected static string $resource = GpsCommandResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}

<?php

namespace App\Filament\Resources\KmLogResource\Pages;

use App\Filament\Resources\KmLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListKmLogs extends ListRecords
{
    protected static string $resource = KmLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

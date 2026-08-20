<?php

namespace App\Filament\Resources\KmLogResource\Pages;

use App\Filament\Resources\KmLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewKmLog extends ViewRecord
{
    protected static string $resource = KmLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}

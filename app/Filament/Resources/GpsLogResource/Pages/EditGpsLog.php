<?php

namespace App\Filament\Resources\GpsLogResource\Pages;

use App\Filament\Resources\GpsLogResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGpsLog extends EditRecord
{
    protected static string $resource = GpsLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}

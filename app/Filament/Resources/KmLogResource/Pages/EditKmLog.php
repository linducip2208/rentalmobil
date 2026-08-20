<?php

namespace App\Filament\Resources\KmLogResource\Pages;

use App\Filament\Resources\KmLogResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditKmLog extends EditRecord
{
    protected static string $resource = KmLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}

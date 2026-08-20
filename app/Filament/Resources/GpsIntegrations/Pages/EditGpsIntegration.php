<?php

namespace App\Filament\Resources\GpsIntegrations\Pages;

use App\Filament\Resources\GpsIntegrations\GpsIntegrationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditGpsIntegration extends EditRecord
{
    protected static string $resource = GpsIntegrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

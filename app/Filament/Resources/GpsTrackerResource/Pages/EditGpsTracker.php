<?php

namespace App\Filament\Resources\GpsTrackerResource\Pages;

use App\Filament\Resources\GpsTrackerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGpsTracker extends EditRecord
{
    protected static string $resource = GpsTrackerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

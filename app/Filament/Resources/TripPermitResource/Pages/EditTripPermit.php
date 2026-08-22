<?php

namespace App\Filament\Resources\TripPermitResource\Pages;

use App\Filament\Resources\TripPermitResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTripPermit extends EditRecord
{
    protected static string $resource = TripPermitResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}

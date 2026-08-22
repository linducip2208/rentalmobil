<?php

namespace App\Filament\Resources\TripPermitResource\Pages;

use App\Filament\Resources\TripPermitResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTripPermits extends ListRecords
{
    protected static string $resource = TripPermitResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}

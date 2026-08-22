<?php

namespace App\Filament\Resources\TripPermitResource\Pages;

use App\Filament\Resources\TripPermitResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTripPermit extends CreateRecord
{
    protected static string $resource = TripPermitResource::class;

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'SPJ dibuat. Cetak PDF untuk dibawa supir.';
    }
}

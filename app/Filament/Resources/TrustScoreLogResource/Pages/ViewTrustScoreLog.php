<?php

namespace App\Filament\Resources\TrustScoreLogResource\Pages;

use App\Filament\Resources\TrustScoreLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewTrustScoreLog extends ViewRecord
{
    protected static string $resource = TrustScoreLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}

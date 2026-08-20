<?php

namespace App\Filament\Resources\TrustScoreLogResource\Pages;

use App\Filament\Resources\TrustScoreLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTrustScoreLogs extends ListRecords
{
    protected static string $resource = TrustScoreLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

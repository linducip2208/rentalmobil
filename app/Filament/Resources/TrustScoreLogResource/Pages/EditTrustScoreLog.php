<?php

namespace App\Filament\Resources\TrustScoreLogResource\Pages;

use App\Filament\Resources\TrustScoreLogResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTrustScoreLog extends EditRecord
{
    protected static string $resource = TrustScoreLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}

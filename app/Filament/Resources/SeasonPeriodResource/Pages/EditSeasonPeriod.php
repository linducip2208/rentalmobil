<?php

namespace App\Filament\Resources\SeasonPeriodResource\Pages;

use App\Filament\Resources\SeasonPeriodResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSeasonPeriod extends EditRecord
{
    protected static string $resource = SeasonPeriodResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}

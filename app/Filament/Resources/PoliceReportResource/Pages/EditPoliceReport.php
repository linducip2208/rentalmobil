<?php

namespace App\Filament\Resources\PoliceReportResource\Pages;

use App\Filament\Resources\PoliceReportResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPoliceReport extends EditRecord
{
    protected static string $resource = PoliceReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources\PoliceReportResource\Pages;

use App\Filament\Resources\PoliceReportResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPoliceReports extends ListRecords
{
    protected static string $resource = PoliceReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

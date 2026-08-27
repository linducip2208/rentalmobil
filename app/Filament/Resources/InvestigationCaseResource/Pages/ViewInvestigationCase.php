<?php

namespace App\Filament\Resources\InvestigationCaseResource\Pages;

use App\Filament\Resources\InvestigationCaseResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewInvestigationCase extends ViewRecord
{
    protected static string $resource = InvestigationCaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}

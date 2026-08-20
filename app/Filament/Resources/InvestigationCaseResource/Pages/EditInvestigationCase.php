<?php

namespace App\Filament\Resources\InvestigationCaseResource\Pages;

use App\Filament\Resources\InvestigationCaseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInvestigationCase extends EditRecord
{
    protected static string $resource = InvestigationCaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
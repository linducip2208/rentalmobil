<?php

namespace App\Filament\Resources\CorporateAccountResource\Pages;

use App\Filament\Resources\CorporateAccountResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCorporateAccounts extends ListRecords
{
    protected static string $resource = CorporateAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}

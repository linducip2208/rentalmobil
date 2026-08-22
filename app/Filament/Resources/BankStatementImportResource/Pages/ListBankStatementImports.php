<?php

namespace App\Filament\Resources\BankStatementImportResource\Pages;

use App\Filament\Resources\BankStatementImportResource;
use Filament\Resources\Pages\ListRecords;

class ListBankStatementImports extends ListRecords
{
    protected static string $resource = BankStatementImportResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()->label('Import CSV')];
    }
}

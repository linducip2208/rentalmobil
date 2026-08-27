<?php

namespace App\Filament\Resources\ReturnRecordResource\Pages;

use App\Filament\Resources\ReturnRecordResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListReturnRecords extends ListRecords
{
    protected static string $resource = ReturnRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

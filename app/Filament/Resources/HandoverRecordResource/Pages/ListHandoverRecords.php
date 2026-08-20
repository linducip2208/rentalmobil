<?php

namespace App\Filament\Resources\HandoverRecordResource\Pages;

use App\Filament\Resources\HandoverRecordResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHandoverRecords extends ListRecords
{
    protected static string $resource = HandoverRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

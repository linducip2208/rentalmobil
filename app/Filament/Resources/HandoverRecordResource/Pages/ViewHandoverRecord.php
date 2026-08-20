<?php

namespace App\Filament\Resources\HandoverRecordResource\Pages;

use App\Filament\Resources\HandoverRecordResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewHandoverRecord extends ViewRecord
{
    protected static string $resource = HandoverRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}

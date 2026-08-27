<?php

namespace App\Filament\Resources\ReturnRecordResource\Pages;

use App\Filament\Resources\ReturnRecordResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewReturnRecord extends ViewRecord
{
    protected static string $resource = ReturnRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}

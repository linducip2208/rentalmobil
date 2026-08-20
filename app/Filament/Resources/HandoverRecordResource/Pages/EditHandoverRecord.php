<?php

namespace App\Filament\Resources\HandoverRecordResource\Pages;

use App\Filament\Resources\HandoverRecordResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHandoverRecord extends EditRecord
{
    protected static string $resource = HandoverRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources\ReturnRecordResource\Pages;

use App\Filament\Resources\ReturnRecordResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditReturnRecord extends EditRecord
{
    protected static string $resource = ReturnRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
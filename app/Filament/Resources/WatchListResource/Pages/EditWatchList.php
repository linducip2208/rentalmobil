<?php

namespace App\Filament\Resources\WatchListResource\Pages;

use App\Filament\Resources\WatchListResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWatchList extends EditRecord
{
    protected static string $resource = WatchListResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}

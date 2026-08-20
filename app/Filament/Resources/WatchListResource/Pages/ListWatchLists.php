<?php

namespace App\Filament\Resources\WatchListResource\Pages;

use App\Filament\Resources\WatchListResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWatchLists extends ListRecords
{
    protected static string $resource = WatchListResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

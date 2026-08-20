<?php

namespace App\Filament\Resources\RentalOrderResource\Pages;

use App\Filament\Resources\RentalOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRentalOrders extends ListRecords
{
    protected static string $resource = RentalOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
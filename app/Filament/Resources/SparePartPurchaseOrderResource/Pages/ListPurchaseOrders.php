<?php

namespace App\Filament\Resources\SparePartPurchaseOrderResource\Pages;

use App\Filament\Resources\SparePartPurchaseOrderResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPurchaseOrders extends ListRecords
{
    protected static string $resource = SparePartPurchaseOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}

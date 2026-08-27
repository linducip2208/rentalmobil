<?php

namespace App\Filament\Resources\SparePartPurchaseOrderResource\Pages;

use App\Filament\Resources\SparePartPurchaseOrderResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePurchaseOrder extends CreateRecord
{
    protected static string $resource = SparePartPurchaseOrderResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['status'] = 'draft';
        $data['created_by'] = auth()->id();

        return $data;
    }
}

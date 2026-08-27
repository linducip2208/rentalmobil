<?php

namespace App\Filament\Resources\PurchaseRequisitionResource\Pages;

use App\Filament\Resources\PurchaseRequisitionResource;
use Filament\Resources\Pages\EditRecord;

class EditPurchaseRequisition extends EditRecord
{
    protected static string $resource = PurchaseRequisitionResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['estimated_total'] = collect($data['items'] ?? [])->sum(fn ($i) => (float) ($i['estimated_total'] ?? 0));

        return $data;
    }
}

<?php

namespace App\Filament\Resources\PurchaseRequisitionResource\Pages;

use App\Filament\Resources\PurchaseRequisitionResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePurchaseRequisition extends CreateRecord
{
    protected static string $resource = PurchaseRequisitionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['status'] = 'draft';
        $data['requested_by'] ??= auth()->id();
        $data['estimated_total'] = collect($data['items'] ?? [])->sum(fn ($i) => (float) ($i['estimated_total'] ?? 0));

        return $data;
    }
}

<?php

namespace App\Filament\Resources\GpsCommandResource\Pages;

use App\Filament\Resources\GpsCommandResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateGpsCommand extends CreateRecord
{
    protected static string $resource = GpsCommandResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['requested_by'] = auth()->id();
        $data['status'] = 'pending_approval';
        $data['idempotency_key'] = (string) Str::uuid();

        return $data;
    }
}

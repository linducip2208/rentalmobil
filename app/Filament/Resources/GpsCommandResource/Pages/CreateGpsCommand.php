<?php
namespace App\Filament\Resources\GpsCommandResource\Pages;
use App\Filament\Resources\GpsCommandResource;
use Filament\Resources\Pages\CreateRecord;
class CreateGpsCommand extends CreateRecord
{
    protected static string $resource = GpsCommandResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array { $data['requested_by'] = auth()->id(); $data['status'] = 'pending_approval'; $data['idempotency_key'] = (string) \Illuminate\Support\Str::uuid(); return $data; }
}

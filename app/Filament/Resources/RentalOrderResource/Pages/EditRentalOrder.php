<?php

namespace App\Filament\Resources\RentalOrderResource\Pages;

use App\Filament\Resources\RentalOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRentalOrder extends EditRecord
{
    protected static string $resource = RentalOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
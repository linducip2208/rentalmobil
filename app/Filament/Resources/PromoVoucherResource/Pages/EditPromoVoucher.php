<?php

namespace App\Filament\Resources\PromoVoucherResource\Pages;

use App\Filament\Resources\PromoVoucherResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPromoVoucher extends EditRecord
{
    protected static string $resource = PromoVoucherResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}

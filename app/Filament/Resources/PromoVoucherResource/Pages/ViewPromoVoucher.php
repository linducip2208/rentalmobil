<?php

namespace App\Filament\Resources\PromoVoucherResource\Pages;

use App\Filament\Resources\PromoVoucherResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPromoVoucher extends ViewRecord
{
    protected static string $resource = PromoVoucherResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}

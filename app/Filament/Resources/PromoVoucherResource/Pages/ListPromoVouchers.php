<?php

namespace App\Filament\Resources\PromoVoucherResource\Pages;

use App\Filament\Resources\PromoVoucherResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPromoVouchers extends ListRecords
{
    protected static string $resource = PromoVoucherResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
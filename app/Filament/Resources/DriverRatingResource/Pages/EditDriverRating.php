<?php

namespace App\Filament\Resources\DriverRatingResource\Pages;

use App\Filament\Resources\DriverRatingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDriverRating extends EditRecord
{
    protected static string $resource = DriverRatingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources\BookingResource\Pages;

use App\Filament\Resources\BookingResource;
use App\Services\BookingService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateBooking extends CreateRecord
{
    protected static string $resource = BookingResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(BookingService::class)->createBooking($data);
    }
}

<?php

namespace App\Filament\Resources\ReturnRecordResource\Pages;

use App\Filament\Resources\ReturnRecordResource;
use App\Models\RentalOrder;
use App\Services\ReturnProcessingService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateReturnRecord extends CreateRecord
{
    protected static string $resource = ReturnRecordResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $order = RentalOrder::findOrFail($data['rental_order_id']);

        return app(ReturnProcessingService::class)->processReturn($order, $data);
    }
}

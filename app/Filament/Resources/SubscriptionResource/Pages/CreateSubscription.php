<?php

namespace App\Filament\Resources\SubscriptionResource\Pages;

use App\Filament\Resources\SubscriptionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSubscription extends CreateRecord
{
    protected static string $resource = SubscriptionResource::class;

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Langganan dibuat. Invoice periode pertama diterbitkan lewat aksi "Tagih sekarang".';
    }
}

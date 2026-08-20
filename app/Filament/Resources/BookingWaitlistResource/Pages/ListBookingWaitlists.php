<?php
namespace App\Filament\Resources\BookingWaitlistResource\Pages;
use App\Filament\Resources\BookingWaitlistResource; use Filament\Actions\CreateAction; use Filament\Resources\Pages\ListRecords;
class ListBookingWaitlists extends ListRecords { protected static string $resource=BookingWaitlistResource::class; protected function getHeaderActions():array{return[CreateAction::make()];} }

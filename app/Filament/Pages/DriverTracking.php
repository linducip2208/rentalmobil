<?php

namespace App\Filament\Pages;

use App\Models\GpsTracker;
use Filament\Pages\Page;

class DriverTracking extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-device-phone-mobile';

    protected static \UnitEnum|string|null $navigationGroup = '📡 GPS & Monitoring';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Pelacakan Driver';

    protected static ?string $title = 'Driver GPS Tracking';

    protected string $view = 'filament.pages.driver-tracking';
}

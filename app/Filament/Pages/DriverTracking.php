<?php

namespace App\Filament\Pages;

use App\Models\GpsTracker;
use Filament\Pages\Page;

class DriverTracking extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-device-phone-mobile';

    protected static \UnitEnum|string|null $navigationGroup = '🔧 Operasional';

    protected static ?int $navigationSort = 10;

    protected static ?string $navigationLabel = 'Driver Tracking';

    protected static ?string $title = 'Driver GPS Tracking';

    protected string $view = 'filament.pages.driver-tracking';
}

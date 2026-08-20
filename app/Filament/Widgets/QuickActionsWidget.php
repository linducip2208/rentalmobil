<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class QuickActionsWidget extends Widget
{
    protected ?string $heading = 'Aksi Cepat';

    protected static ?int $sort = 9;

    protected string $view = 'filament.widgets.quick-actions';

    public static function canView(): bool
    {
        return true;
    }
}

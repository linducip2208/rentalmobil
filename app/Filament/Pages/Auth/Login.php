<?php

namespace App\Filament\Pages\Auth;

class Login extends \Filament\Auth\Pages\Login
{
    protected string $view = 'filament.pages.auth.login';

    public function hasLogo(): bool
    {
        return false;
    }
}

<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(\App\Filament\Pages\Auth\Login::class)
            ->simplePageMaxContentWidth('full')
            ->brandName('RentalMobil')
            ->favicon(asset('favicon.ico'))
            ->colors([
                'primary' => Color::Blue,
                'success' => Color::Emerald,
                'warning' => Color::Amber,
                'danger' => Color::Rose,
                'info' => Color::Sky,
                'gray' => Color::Stone,
            ])
            ->font('Inter')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->sidebarCollapsibleOnDesktop()
            ->sidebarWidth('15.5rem')
            ->collapsedSidebarWidth('4rem')
            ->darkMode(true)
            ->topbar(true)
            ->maxContentWidth('full')
            ->navigationGroups([
                NavigationGroup::make('Dashboard')->icon('heroicon-o-squares-2x2')->collapsed(false),
                NavigationGroup::make('Rental')->icon('heroicon-o-calendar-days')->collapsed(false),
                NavigationGroup::make('Fleet')->icon('heroicon-o-truck')->collapsed(false),
                NavigationGroup::make('Maintenance')->icon('heroicon-o-wrench-screwdriver')->collapsed(true),
                NavigationGroup::make('Customers')->icon('heroicon-o-users')->collapsed(true),
                NavigationGroup::make('GPS & Monitoring')->icon('heroicon-o-signal')->collapsed(true),
                NavigationGroup::make('Finance')->icon('heroicon-o-banknotes')->collapsed(true),
                NavigationGroup::make('Procurement & Inventory')->icon('heroicon-o-archive-box')->collapsed(true),
                NavigationGroup::make('Risk & Security')->icon('heroicon-o-shield-exclamation')->collapsed(true),
                NavigationGroup::make('Sales & Marketing')->icon('heroicon-o-megaphone')->collapsed(true),
                NavigationGroup::make('CMS')->icon('heroicon-o-window')->collapsed(true),
                NavigationGroup::make('Reports')->icon('heroicon-o-chart-bar-square')->collapsed(true),
                NavigationGroup::make('Settings')->icon('heroicon-o-cog-8-tooth')->collapsed(true),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                \App\Http\Middleware\EnsureAdminRoleAccess::class,
            ]);
    }
}

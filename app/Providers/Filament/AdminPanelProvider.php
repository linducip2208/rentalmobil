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
            ->login()
            ->brandName('RentalMobil')
            ->favicon(asset('favicon.ico'))
            ->colors([
                'primary' => Color::Indigo,
                'success' => Color::Emerald,
                'warning' => Color::Amber,
                'danger' => Color::Rose,
                'info' => Color::Sky,
                'gray' => Color::Stone,
            ])
            ->font('Inter')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->sidebarCollapsibleOnDesktop()
            ->darkMode(true)
            ->maxContentWidth('full')
            ->navigationGroups([
                NavigationGroup::make('🚗 Master Data')->collapsed(false),
                NavigationGroup::make('📋 Penjualan')->collapsed(false),
                NavigationGroup::make('💰 Keuangan')->collapsed(true),
                NavigationGroup::make('🔧 Operasional')->collapsed(true),
                NavigationGroup::make('🛡️ Security')->collapsed(true),
                NavigationGroup::make('📊 Laporan')->collapsed(true),
                NavigationGroup::make('📢 Marketing')->collapsed(true),
                NavigationGroup::make('🔌 Integrasi')->collapsed(true),
                NavigationGroup::make('⚙️ Sistem')->collapsed(true),
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
            ]);
    }
}

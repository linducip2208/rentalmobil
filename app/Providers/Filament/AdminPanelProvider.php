<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\Login;
use App\Filament\Widgets\FleetDispatchWidget;
use App\Filament\Widgets\PendingApprovalsWidget;
use App\Filament\Widgets\QuickActionsWidget;
use App\Filament\Widgets\RecentOrdersTableWidget;
use App\Filament\Widgets\RevenueChartWidget;
use App\Filament\Widgets\StatsOverviewWidget;
use App\Http\Middleware\EnsureAdminRoleAccess;
use App\Services\WhitelabelService;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Contracts\View\View;
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
        $brand = app(WhitelabelService::class);

        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(Login::class)
            ->simplePageMaxContentWidth('full')
            ->brandName(fn (): string => $brand->name())
            ->brandLogo(fn (): ?string => $brand->asset('brand_logo'))
            ->darkModeBrandLogo(fn (): ?string => $brand->asset('brand_logo_dark', $brand->asset('brand_logo')))
            ->brandLogoHeight('2.25rem')
            ->favicon(fn (): ?string => $brand->asset('brand_favicon', asset('favicon.ico')))
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
            ->spa()
            ->maxContentWidth('full')
            ->navigationGroups([
                // Group icons intentionally stay empty: Filament suppresses
                // child resource icons whenever a group itself has an icon.
                NavigationGroup::make('Dashboard')->collapsed(false),
                NavigationGroup::make('Rental')->collapsed(true),
                NavigationGroup::make('Fleet')->collapsed(true),
                NavigationGroup::make('Customers')->collapsed(true),
                NavigationGroup::make('GPS & Monitoring')->collapsed(true),
                NavigationGroup::make('Maintenance')->collapsed(true),
                NavigationGroup::make('Finance')->collapsed(true),
                NavigationGroup::make('Procurement & Inventory')->collapsed(true),
                NavigationGroup::make('Reports')->collapsed(true),
                NavigationGroup::make('CMS & Marketing')->collapsed(true),
                NavigationGroup::make('Settings')->collapsed(true),
            ])
            ->renderHook(PanelsRenderHook::HEAD_END, fn (): View => view('filament.components.admin-theme-settings'))
            ->renderHook(PanelsRenderHook::TOPBAR_END, fn (): View => view('filament.components.command-palette'))
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->widgets([
                StatsOverviewWidget::class,
                FleetDispatchWidget::class,
                QuickActionsWidget::class,
                PendingApprovalsWidget::class,
                RevenueChartWidget::class,
                RecentOrdersTableWidget::class,
            ])
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
                EnsureAdminRoleAccess::class,
            ]);
    }
}

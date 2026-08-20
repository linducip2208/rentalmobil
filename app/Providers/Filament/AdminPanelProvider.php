<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
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
            ->brandLogo(fn () => view('filament.brand-logo'))
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
            ->resources([
                \App\Filament\Resources\CategoryResource::class,
                \App\Filament\Resources\BrandResource::class,
                \App\Filament\Resources\LocationResource::class,
                \App\Filament\Resources\VehicleResource::class,
                \App\Filament\Resources\CustomerResource::class,
                \App\Filament\Resources\DriverResource::class,
                \App\Filament\Resources\PaymentMethodResource::class,
                \App\Filament\Resources\AddonResource::class,
                \App\Filament\Resources\BookingResource::class,
                \App\Filament\Resources\RentalOrderResource::class,
                \App\Filament\Resources\InvoiceResource::class,
                \App\Filament\Resources\PaymentResource::class,
                \App\Filament\Resources\ReturnRecordResource::class,
                \App\Filament\Resources\DamageReportResource::class,
                \App\Filament\Resources\PromoVoucherResource::class,
                \App\Filament\Resources\ChartOfAccountResource::class,
                \App\Filament\Resources\JournalEntryResource::class,
                \App\Filament\Resources\ExpenseResource::class,
                \App\Filament\Resources\ExpenseCategoryResource::class,
                \App\Filament\Resources\BankAccountResource::class,
                \App\Filament\Resources\MaintenanceLogResource::class,
                \App\Filament\Resources\ServiceScheduleResource::class,
                \App\Filament\Resources\FuelLogResource::class,
                \App\Filament\Resources\DeliveryResource::class,
                \App\Filament\Resources\TransferResource::class,
                \App\Filament\Resources\BlacklistEntryResource::class,
                \App\Filament\Resources\InvestigationCaseResource::class,
                \App\Filament\Resources\BlogPostResource::class,
                \App\Filament\Resources\TestimonialResource::class,
                \App\Filament\Resources\FaqResource::class,
                \App\Filament\Resources\UserResource::class,
            ])
            ->widgets([
                \App\Filament\Widgets\StatsOverviewWidget::class,
                \App\Filament\Widgets\RevenueChartWidget::class,
                \App\Filament\Widgets\OrderStatusChartWidget::class,
                \App\Filament\Widgets\RecentOrdersTableWidget::class,
                \App\Filament\Widgets\OverdueOrdersTableWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}

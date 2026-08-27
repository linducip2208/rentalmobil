<?php

namespace App\Filament\Pages;

use App\Services\SystemSettingService;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class Settings extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static \UnitEnum|string|null $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Pengaturan';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.settings';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return (bool) $user?->is_active && (
            in_array($user->role, ['super_admin', 'owner'], true)
            || $user->hasAnyRole(['Super Admin', 'Owner'])
            || $user->can('update.system_setting')
        );
    }

    public function mount(SystemSettingService $settings): void
    {
        abort_unless(static::canAccess(), 403);
        $this->form->fill($settings->values());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('settings')
                    ->persistTabInQueryString()
                    ->tabs([
                        Tab::make('Company Profile')->icon('heroicon-o-building-office-2')->schema([
                            Section::make('Identitas perusahaan')->columns(2)->schema([
                                TextInput::make('company_name')->label('Nama perusahaan')->required(),
                                TextInput::make('company_legal_name')->label('Nama legal'),
                                TextInput::make('company_npwp')->label('NPWP'),
                                TextInput::make('company_email')->label('Email')->email(),
                                TextInput::make('company_phone')->label('Telepon'),
                                TextInput::make('company_whatsapp')->label('WhatsApp'),
                                Textarea::make('company_address')->label('Alamat')->columnSpanFull(),
                            ]),
                        ]),
                        Tab::make('Application & Branding')->icon('heroicon-o-swatch')->schema([
                            Section::make('Application')->columns(3)->schema([
                                Select::make('timezone')->options(['Asia/Jakarta' => 'WIB — Jakarta', 'Asia/Makassar' => 'WITA — Makassar', 'Asia/Jayapura' => 'WIT — Jayapura'])->required(),
                                Select::make('locale')->options(['id' => 'Bahasa Indonesia', 'en' => 'English'])->required(),
                                TextInput::make('date_format')->label('Format tanggal')->required(),
                            ]),
                            Section::make('Branding')->columns(2)->schema([
                                FileUpload::make('brand_logo')->label('Logo')->image()->disk('public')->directory('branding'),
                                FileUpload::make('brand_favicon')->label('Favicon')->image()->disk('public')->directory('branding'),
                                ColorPicker::make('primary_color')->label('Warna utama')->required(),
                            ]),
                        ]),
                        Tab::make('Rental & Booking')->icon('heroicon-o-calendar-days')->schema([
                            Section::make('Rental Rules')->columns(3)->schema([
                                TextInput::make('minimum_rental_days')->label('Minimum sewa (hari)')->numeric()->required(),
                                TextInput::make('grace_period_minutes')->label('Grace period (menit)')->numeric()->required(),
                                TextInput::make('kilometer_limit_daily')->label('Batas KM/hari')->numeric()->required(),
                                TextInput::make('kilometer_excess_fee')->label('Biaya kelebihan/KM')->numeric()->prefix('Rp')->required(),
                                TextInput::make('overtime_fee_hourly')->label('Overtime/jam')->numeric()->prefix('Rp')->required(),
                                TextInput::make('late_fee_hourly')->label('Denda terlambat/jam')->numeric()->prefix('Rp')->required(),
                                Textarea::make('rental_terms')->label('Syarat & ketentuan')->columnSpanFull(),
                            ]),
                            Section::make('Booking Rules')->columns(2)->schema([
                                TextInput::make('booking_expiry_minutes')->label('Masa berlaku booking (menit)')->numeric()->required(),
                                TextInput::make('booking_minimum_notice_hours')->label('Minimum pemesanan (jam)')->numeric()->required(),
                            ]),
                        ]),
                        Tab::make('Finance')->icon('heroicon-o-banknotes')->schema([
                            Section::make('Finance & Tax')->columns(3)->schema([
                                TextInput::make('currency')->label('Mata uang')->required()->maxLength(3),
                                TextInput::make('currency_symbol')->label('Simbol')->required(),
                                TextInput::make('currency_decimal_places')->label('Desimal')->numeric()->required(),
                                TextInput::make('tax_rate')->label('Tarif pajak')->numeric()->suffix('desimal')->helperText('Contoh 0.11 untuk 11%')->required(),
                                TextInput::make('default_deposit')->label('Deposit default')->numeric()->prefix('Rp')->required(),
                                TextInput::make('invoice_due_days')->label('Jatuh tempo invoice (hari)')->numeric()->required(),
                            ]),
                            Section::make('Numbering')->columns(4)->schema([
                                TextInput::make('quotation_prefix')->label('Quotation')->required(),
                                TextInput::make('booking_prefix')->label('Booking')->required(),
                                TextInput::make('rental_order_prefix')->label('Rental Order')->required(),
                                TextInput::make('invoice_prefix')->label('Invoice')->required(),
                            ]),
                            Section::make('Invoice')->columns(2)->schema([
                                FileUpload::make('invoice_logo')->image()->disk('public')->directory('branding'),
                                FileUpload::make('invoice_signature')->image()->disk('public')->directory('branding'),
                                Textarea::make('invoice_footer')->columnSpanFull(),
                            ]),
                        ]),
                        Tab::make('Vehicle & Maintenance')->icon('heroicon-o-wrench-screwdriver')->schema([
                            Section::make('Vehicle & Inventory')->columns(2)->schema([
                                Toggle::make('allow_negative_stock')->label('Izinkan stok negatif')->helperText('Disarankan tetap nonaktif.'),
                                TextInput::make('maintenance_warning_days')->label('Peringatan maintenance (hari)')->numeric()->required(),
                            ]),
                        ]),
                        Tab::make('Notification & Integration')->icon('heroicon-o-bell-alert')->schema([
                            Section::make('Notification')->columns(3)->schema([
                                Toggle::make('notification_email_enabled')->label('Email'),
                                Toggle::make('notification_whatsapp_enabled')->label('WhatsApp'),
                                Toggle::make('notification_sms_enabled')->label('SMS'),
                            ])->description('Credential dan endpoint dikelola melalui menu Providers agar tidak hardcoded dan tetap terenkripsi.'),
                        ]),
                        Tab::make('SEO')->icon('heroicon-o-globe-alt')->schema([
                            Section::make('Global SEO')->columns(2)->schema([
                                TextInput::make('seo_site_title')->label('Site title')->maxLength(70),
                                TextInput::make('seo_title_suffix')->label('Title suffix')->maxLength(40),
                                Textarea::make('seo_default_description')->label('Default description')->maxLength(170)->columnSpanFull(),
                                TextInput::make('seo_canonical_base_url')->label('Canonical base URL')->url(),
                                FileUpload::make('seo_default_og_image')->label('Default OG image')->image()->disk('public')->directory('seo'),
                                Textarea::make('seo_robots')->label('Robots directives')->columnSpanFull(),
                            ]),
                        ]),
                        Tab::make('Security & Backup')->icon('heroicon-o-shield-check')->schema([
                            Section::make('Security')->columns(2)->schema([
                                TextInput::make('security_max_login_attempts')->label('Maksimum login gagal')->numeric()->required(),
                                TextInput::make('security_session_timeout_minutes')->label('Session timeout (menit)')->numeric()->required(),
                            ]),
                            Section::make('Backup')->schema([
                                TextInput::make('backup_retention_days')->label('Retensi backup (hari)')->numeric()->required(),
                            ]),
                        ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(SystemSettingService $settings): void
    {
        abort_unless(static::canAccess(), 403);
        $settings->save($this->form->getState());

        Notification::make()->title('Pengaturan berhasil disimpan')->success()->send();
    }
}

<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\SystemSetting;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SystemSettingService
{
    /** @return array<string, array{group:string,type:string,default:mixed,rules:list<string>}> */
    public function definitions(): array
    {
        return [
            'company_name' => $this->definition('company', 'string', 'RentalMobil', ['nullable', 'string', 'max:160']),
            'company_legal_name' => $this->definition('company', 'string', '', ['nullable', 'string', 'max:200']),
            'company_npwp' => $this->definition('company', 'string', '', ['nullable', 'string', 'max:40']),
            'company_address' => $this->definition('company', 'text', '', ['nullable', 'string', 'max:2000']),
            'company_phone' => $this->definition('company', 'string', '', ['nullable', 'string', 'max:40']),
            'company_whatsapp' => $this->definition('company', 'string', '', ['nullable', 'string', 'max:40']),
            'company_email' => $this->definition('company', 'string', '', ['nullable', 'email', 'max:160']),
            'timezone' => $this->definition('application', 'string', 'Asia/Jakarta', ['required', 'timezone']),
            'locale' => $this->definition('application', 'string', 'id', ['required', 'in:id,en']),
            'date_format' => $this->definition('application', 'string', 'd/m/Y', ['required', 'string', 'max:30']),
            'currency' => $this->definition('finance', 'string', 'IDR', ['required', 'string', 'size:3']),
            'currency_symbol' => $this->definition('finance', 'string', 'Rp', ['required', 'string', 'max:8']),
            'currency_decimal_places' => $this->definition('finance', 'integer', 0, ['required', 'integer', 'between:0,4']),
            'brand_logo' => $this->definition('branding', 'string', '', ['nullable', 'string', 'max:500']),
            'brand_logo_dark' => $this->definition('branding', 'string', '', ['nullable', 'string', 'max:500']),
            'brand_favicon' => $this->definition('branding', 'string', '', ['nullable', 'string', 'max:500']),
            'brand_name' => $this->definition('branding', 'string', 'RentalMobil', ['required', 'string', 'max:80']),
            'brand_short_name' => $this->definition('branding', 'string', 'RentalMobil', ['required', 'string', 'max:30']),
            'brand_tagline' => $this->definition('branding', 'string', 'Perjalanan mudah, operasi terkendali.', ['nullable', 'string', 'max:160']),
            'brand_copyright' => $this->definition('branding', 'string', 'Hak cipta dilindungi.', ['nullable', 'string', 'max:200']),
            'show_powered_by' => $this->definition('branding', 'boolean', false, ['required', 'boolean']),
            'public_font' => $this->definition('branding', 'string', 'Inter', ['required', 'in:Inter,Manrope,Plus Jakarta Sans,DM Sans,System UI']),
            'login_eyebrow' => $this->definition('branding', 'string', 'Fleet operations platform', ['nullable', 'string', 'max:80']),
            'login_headline' => $this->definition('branding', 'string', 'Kendaraan bergerak. Operasi tetap terkendali.', ['nullable', 'string', 'max:160']),
            'login_description' => $this->definition('branding', 'text', 'Kelola reservasi, serah-terima, GPS, keuangan, dan risiko dalam satu pusat kendali rental.', ['nullable', 'string', 'max:500']),
            'invoice_logo' => $this->definition('branding', 'string', '', ['nullable', 'string', 'max:500']),
            'invoice_signature' => $this->definition('branding', 'string', '', ['nullable', 'string', 'max:500']),
            'primary_color' => $this->definition('branding', 'string', '#2563eb', ['required', 'regex:/^#[0-9A-Fa-f]{6}$/']),
            'admin_font' => $this->definition('branding', 'string', 'Inter', ['required', 'in:Inter,Manrope,Plus Jakarta Sans,DM Sans,System UI']),
            'admin_sidebar_color' => $this->definition('branding', 'string', '#0b1426', ['required', 'regex:/^#[0-9A-Fa-f]{6}$/']),
            'admin_sidebar_end_color' => $this->definition('branding', 'string', '#101c31', ['required', 'regex:/^#[0-9A-Fa-f]{6}$/']),
            'admin_sidebar_text_color' => $this->definition('branding', 'string', '#e2e8f0', ['required', 'regex:/^#[0-9A-Fa-f]{6}$/']),
            'admin_sidebar_muted_color' => $this->definition('branding', 'string', '#94a3b8', ['required', 'regex:/^#[0-9A-Fa-f]{6}$/']),
            'admin_sidebar_icon_color' => $this->definition('branding', 'string', '#7dd3fc', ['required', 'regex:/^#[0-9A-Fa-f]{6}$/']),
            'admin_active_menu_color' => $this->definition('branding', 'string', '#2563eb', ['required', 'regex:/^#[0-9A-Fa-f]{6}$/']),
            'admin_accent_color' => $this->definition('branding', 'string', '#f59e0b', ['required', 'regex:/^#[0-9A-Fa-f]{6}$/']),
            'admin_content_background' => $this->definition('branding', 'string', '#f4f7f9', ['required', 'regex:/^#[0-9A-Fa-f]{6}$/']),
            'minimum_rental_days' => $this->definition('rental', 'integer', 1, ['required', 'integer', 'min:1', 'max:365']),
            'grace_period_minutes' => $this->definition('rental', 'integer', 30, ['required', 'integer', 'min:0', 'max:1440']),
            'kilometer_limit_daily' => $this->definition('rental', 'integer', 0, ['required', 'integer', 'min:0']),
            'kilometer_excess_fee' => $this->definition('rental', 'decimal', 0, ['required', 'numeric', 'min:0']),
            'overtime_fee_hourly' => $this->definition('rental', 'decimal', 0, ['required', 'numeric', 'min:0']),
            'booking_expiry_minutes' => $this->definition('booking', 'integer', 30, ['required', 'integer', 'min:5', 'max:10080']),
            'booking_minimum_notice_hours' => $this->definition('booking', 'integer', 2, ['required', 'integer', 'min:0', 'max:8760']),
            'allow_negative_stock' => $this->definition('inventory', 'boolean', false, ['required', 'boolean']),
            'inventory_cost_method' => $this->definition('inventory', 'string', 'average', ['required', 'in:average']),
            'purchase_order_approval_threshold' => $this->definition('procurement', 'decimal', 5000000, ['required', 'numeric', 'min:0']),
            'tax_rate' => $this->definition('tax', 'decimal', 0.11, ['required', 'numeric', 'between:0,1']),
            'default_deposit' => $this->definition('deposit', 'decimal', 0, ['required', 'numeric', 'min:0']),
            'late_fee_hourly' => $this->definition('late_fee', 'decimal', 0, ['required', 'numeric', 'min:0']),
            'invoice_due_days' => $this->definition('invoice', 'integer', 7, ['required', 'integer', 'min:0', 'max:365']),
            'invoice_prefix' => $this->definition('numbering', 'string', 'INV', ['required', 'alpha_dash', 'max:20']),
            'quotation_prefix' => $this->definition('numbering', 'string', 'QUO', ['required', 'alpha_dash', 'max:20']),
            'booking_prefix' => $this->definition('numbering', 'string', 'BKG', ['required', 'alpha_dash', 'max:20']),
            'rental_order_prefix' => $this->definition('numbering', 'string', 'RO', ['required', 'alpha_dash', 'max:20']),
            'invoice_footer' => $this->definition('invoice', 'text', '', ['nullable', 'string', 'max:5000']),
            'rental_terms' => $this->definition('rental', 'text', '', ['nullable', 'string', 'max:20000']),
            'maintenance_warning_days' => $this->definition('maintenance', 'integer', 14, ['required', 'integer', 'min:1', 'max:365']),
            'notification_email_enabled' => $this->definition('notification', 'boolean', true, ['required', 'boolean']),
            'notification_whatsapp_enabled' => $this->definition('notification', 'boolean', false, ['required', 'boolean']),
            'notification_sms_enabled' => $this->definition('notification', 'boolean', false, ['required', 'boolean']),
            'security_max_login_attempts' => $this->definition('security', 'integer', 5, ['required', 'integer', 'between:3,20']),
            'security_session_timeout_minutes' => $this->definition('security', 'integer', 120, ['required', 'integer', 'between:15,1440']),
            'backup_retention_days' => $this->definition('backup', 'integer', 14, ['required', 'integer', 'between:1,365']),
            'seo_site_title' => $this->definition('seo', 'string', 'RentalMobil', ['nullable', 'string', 'max:70']),
            'seo_title_suffix' => $this->definition('seo', 'string', '', ['nullable', 'string', 'max:40']),
            'seo_default_description' => $this->definition('seo', 'text', '', ['nullable', 'string', 'max:170']),
            'seo_canonical_base_url' => $this->definition('seo', 'string', '', ['nullable', 'url', 'max:255']),
            'seo_default_og_image' => $this->definition('seo', 'string', '', ['nullable', 'string', 'max:500']),
            'seo_robots' => $this->definition('seo', 'text', 'index,follow', ['nullable', 'string', 'max:200']),
        ];
    }

    public function values(): array
    {
        return collect($this->definitions())->mapWithKeys(
            fn (array $definition, string $key) => [$key => SystemSetting::get($key, $definition['default'])]
        )->all();
    }

    public function save(array $input): void
    {
        $definitions = $this->definitions();
        $data = Arr::only($input, array_keys($definitions));
        $rules = collect($definitions)->mapWithKeys(fn (array $definition, string $key) => [$key => $definition['rules']])->all();
        $validated = Validator::make($data, $rules)->validate();

        DB::transaction(function () use ($validated, $definitions): void {
            foreach ($validated as $key => $value) {
                $definition = $definitions[$key];
                $serialized = $this->serialize($value, $definition['type']);
                $setting = SystemSetting::query()->firstOrNew(['key' => $key]);
                $old = $setting->exists ? $setting->value : null;

                $setting->fill([
                    'group_name' => $definition['group'],
                    'type' => $definition['type'],
                    'value' => $serialized,
                ])->save();

                if ($old !== $serialized) {
                    AuditLog::create([
                        'user_id' => auth()->id(),
                        'action' => 'setting.updated',
                        'auditable_type' => SystemSetting::class,
                        'auditable_id' => $setting->id,
                        'old_values' => ['value' => $old],
                        'new_values' => ['value' => $serialized],
                        'branch_id' => auth()->user()?->location_id,
                    ]);
                }
            }

            SystemSetting::clearCache();
        });
    }

    private function definition(string $group, string $type, mixed $default, array $rules): array
    {
        return compact('group', 'type', 'default', 'rules');
    }

    private function serialize(mixed $value, string $type): ?string
    {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            'boolean' => $value ? '1' : '0',
            'json' => json_encode($value, JSON_THROW_ON_ERROR),
            default => (string) $value,
        };
    }
}

<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Storage;
use Throwable;

class WhitelabelService
{
    public function get(string $key, mixed $default = null): mixed
    {
        try {
            return SystemSetting::get($key, $default) ?: $default;
        } catch (Throwable) {
            return $default;
        }
    }

    public function name(): string
    {
        return (string) $this->get('brand_name', $this->get('company_name', config('app.name', 'RentalMobil')));
    }

    public function initials(): string
    {
        $words = preg_split('/\s+/u', trim($this->name())) ?: [];
        $initials = implode('', array_map(fn (string $word): string => mb_substr($word, 0, 1), array_slice($words, 0, 2)));

        return mb_strtoupper(mb_substr($initials, 0, 2)) ?: 'RM';
    }

    public function asset(string $key, ?string $fallback = null): ?string
    {
        $path = $this->get($key);

        if (! $path) {
            return $fallback;
        }

        return filter_var($path, FILTER_VALIDATE_URL) ? $path : Storage::disk('public')->url($path);
    }

    /** @return array<string, mixed> */
    public function viewData(): array
    {
        return [
            'name' => $this->name(),
            'initials' => $this->initials(),
            'tagline' => $this->get('brand_tagline', 'Perjalanan mudah, operasi terkendali.'),
            'logo' => $this->asset('brand_logo'),
            'logoDark' => $this->asset('brand_logo_dark', $this->asset('brand_logo')),
            'favicon' => $this->asset('brand_favicon', asset('favicon.ico')),
            'primaryColor' => $this->get('primary_color', '#2563eb'),
            'font' => $this->get('public_font', 'Inter'),
            'copyright' => $this->get('brand_copyright', 'Hak cipta dilindungi.'),
            'showPoweredBy' => (bool) $this->get('show_powered_by', false),
        ];
    }
}

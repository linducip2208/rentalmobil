<?php

namespace Database\Seeders;

use App\Models\Vehicle;
use App\Models\VehiclePhoto;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Demo vehicle media for development/demo environments.
 *
 * Generates original, project-owned placeholder artwork (SVG) so the public
 * storefront and vehicle galleries show real visual media instead of
 * emoji/text placeholders. The artwork lives under
 * storage/app/public/vehicles/demo and is safe to regenerate.
 *
 * Idempotent: vehicles that already have gallery photos are skipped.
 */
class DemoVehicleMediaSeeder extends Seeder
{
    /** @var array<string, string> */
    private const TYPES = [
        'exterior' => 'Tampak Depan',
        'side' => 'Tampak Samping',
        'interior' => 'Interior',
        'dashboard' => 'Dashboard',
        'rear' => 'Tampak Belakang',
    ];

    /** Palettes rotate per vehicle so the fleet does not look uniform. */
    private const PALETTES = [
        ['from' => '#0f172a', 'to' => '#1e3a5f', 'accent' => '#38bdf8'],
        ['from' => '#111827', 'to' => '#312e81', 'accent' => '#818cf8'],
        ['from' => '#0c1f1d', 'to' => '#134e4a', 'accent' => '#2dd4bf'],
        ['from' => '#1c1917', 'to' => '#7c2d12', 'accent' => '#fb923c'],
        ['from' => '#171717', 'to' => '#3f3f46', 'accent' => '#a1a1aa'],
        ['from' => '#0a0f1e', 'to' => '#1d4ed8', 'accent' => '#60a5fa'],
    ];

    public function run(): void
    {
        $paletteIndex = 0;

        foreach (Vehicle::where('is_active', true)->orderBy('id')->get() as $vehicle) {
            if ($vehicle->photos()->exists()) {
                continue;
            }

            $palette = self::PALETTES[$paletteIndex++ % count(self::PALETTES)];

            $order = 1;
            foreach (array_slice(self::TYPES, 0, 4) as $type => $label) {
                $slug = $vehicle->slug.'-'.$type;
                $path = 'vehicles/demo/'.$vehicle->slug.'/'.$type.'.svg';

                if (! Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->put(
                        $path,
                        $this->renderSvg($vehicle->name, $label, $palette)
                    );
                }

                VehiclePhoto::create([
                    'vehicle_id' => $vehicle->id,
                    'photo_url' => $path,
                    'alt_text' => sprintf('%s — %s (foto demo)', $vehicle->name, $label),
                    'caption' => $label,
                    'type' => $type,
                    'disk' => 'public',
                    'sort_order' => $order,
                    'is_primary' => $type === 'exterior',
                ]);

                $order++;
            }

            // Keep the legacy column in sync so legacy consumers also render media.
            $vehicle->update(['photo_url' => 'vehicles/demo/'.$vehicle->slug.'/exterior.svg']);
        }
    }

    /**
     * Render a clean, premium-looking demo frame (800x500) with a minimalist
     * vehicle silhouette. Deterministic per vehicle name.
     *
     * @param  array{from: string, to: string, accent: string}  $palette
     */
    private function renderSvg(string $vehicleName, string $label, array $palette): string
    {
        $safeName = htmlspecialchars($vehicleName, ENT_QUOTES, 'UTF-8');
        $safeLabel = htmlspecialchars($label.' · Foto demo', ENT_QUOTES, 'UTF-8');
        $gradientId = 'g'.md5($vehicleName.$label);

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 500" width="800" height="500" role="img" aria-label="{$safeName}">
  <defs>
    <linearGradient id="{$gradientId}bg" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0" stop-color="{$palette['from']}"/>
      <stop offset="1" stop-color="{$palette['to']}"/>
    </linearGradient>
    <linearGradient id="{$gradientId}car" x1="0" y1="0" x2="0" y2="1">
      <stop offset="0" stop-color="#ffffff" stop-opacity="0.94"/>
      <stop offset="1" stop-color="#e2e8f0" stop-opacity="0.82"/>
    </linearGradient>
    <radialGradient id="{$gradientId}glow" cx="0.5" cy="0.42" r="0.65">
      <stop offset="0" stop-color="{$palette['accent']}" stop-opacity="0.28"/>
      <stop offset="1" stop-color="{$palette['accent']}" stop-opacity="0"/>
    </radialGradient>
  </defs>
  <rect width="800" height="500" fill="url(#{$gradientId}bg)"/>
  <rect width="800" height="500" fill="url(#{$gradientId}glow)"/>
  <g stroke="#ffffff" stroke-opacity="0.05">
    <path d="M0 125 H800 M0 250 H800 M0 375 H800 M200 0 V500 M400 0 V500 M600 0 V500"/>
  </g>
  <ellipse cx="400" cy="392" rx="270" ry="22" fill="#000000" opacity="0.28"/>
  <g>
    <path d="M132 340 C132 300 160 286 216 276 C252 224 306 200 386 198 C470 196 536 218 584 262 C646 268 682 282 690 308 C696 330 678 344 646 346 L586 348 M420 348 L300 348 M214 348 L172 346 C146 344 132 336 132 340 Z" fill="none"/>
    <path d="M132 342 C132 302 162 286 218 277 C254 226 308 202 386 200 C468 198 534 219 582 262 C644 268 684 283 692 310 C698 332 678 346 646 348 L582 350 C582 350 470 350 418 350 C340 350 262 350 216 350 C168 350 132 348 132 342 Z" fill="url(#{$gradientId}car)"/>
    <path d="M262 272 C292 230 336 210 390 208 L392 264 L262 264 Z" fill="#0f172a" opacity="0.85"/>
    <path d="M410 210 C462 212 506 228 540 262 L412 264 L410 210 Z" fill="#0f172a" opacity="0.85"/>
    <rect x="150" y="300" width="140" height="10" rx="5" fill="{$palette['accent']}" opacity="0.85"/>
    <circle cx="252" cy="346" r="46" fill="#0b1220"/>
    <circle cx="252" cy="346" r="20" fill="#94a3b8"/>
    <circle cx="560" cy="346" r="46" fill="#0b1220"/>
    <circle cx="560" cy="346" r="20" fill="#94a3b8"/>
    <rect x="640" y="300" width="42" height="9" rx="4.5" fill="{$palette['accent']}" opacity="0.9"/>
  </g>
  <g font-family="system-ui, 'Segoe UI', sans-serif">
    <text x="48" y="72" fill="#ffffff" font-size="30" font-weight="700" letter-spacing="-0.5">{$safeName}</text>
    <text x="48" y="100" fill="#ffffff" fill-opacity="0.62" font-size="15" font-weight="500">{$safeLabel}</text>
  </g>
  <text x="752" y="472" fill="#ffffff" fill-opacity="0.35" font-family="system-ui, 'Segoe UI', sans-serif" font-size="13" text-anchor="end">Demo media — ganti dengan foto asli via Admin → Fleet → Kendaraan</text>
</svg>
SVG;
    }
}

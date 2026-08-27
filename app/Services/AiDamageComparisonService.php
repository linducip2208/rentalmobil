<?php

namespace App\Services;

use App\Models\DamageComparison;
use App\Models\HandoverRecord;
use App\Models\Provider;
use App\Models\RentalOrder;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Storage;

/**
 * Perbandingan AI antara foto checkout dan foto return:
 * mendeteksi kerusakan BARU yang muncul selama masa sewa.
 */
class AiDamageComparisonService
{
    public function __construct(protected AiVisionService $vision) {}

    public function compare(RentalOrder $order, ?int $providerId = null): DamageComparison
    {
        $checkout = HandoverRecord::where('rental_order_id', $order->id)->where('type', 'checkout')->orderByDesc('recorded_at')->first();
        $return = HandoverRecord::where('rental_order_id', $order->id)->whereIn('type', ['checkin', 'checkin_return'])->orderByDesc('recorded_at')->first();

        abort_unless($checkout && filled($checkout->photos), 422, 'Foto handover (checkout) belum tersedia.');
        abort_unless($return && filled($return->photos), 422, 'Foto return (check-in) belum tersedia.');

        $comparison = DamageComparison::create([
            'rental_order_id' => $order->id,
            'checkout_handover_id' => $checkout->id,
            'return_handover_id' => $return->id,
            'provider_id' => $providerId ?? $this->resolveProviderId(),
            'status' => 'pending',
        ]);

        try {
            $photos = array_slice(array_merge($checkout->photos ?? [], $return->photos ?? []), 0, 8);

            $analysis = $this->vision->analyzeImages(
                Provider::findOrFail($comparison->provider_id),
                $this->normalizePaths($photos),
                $this->comparisonPrompt()
            );

            $findings = is_array($analysis) ? $analysis : [];
            $newDamages = array_values(array_filter($findings, fn ($f) => ($f['is_new'] ?? true) === true));
            $estimatedCost = array_sum(array_map(fn ($f) => (float) ($f['estimated_cost_idr'] ?? 0), $newDamages));

            $comparison->update([
                'analysis' => [
                    'all_findings' => $findings,
                    'new_damages' => $newDamages,
                    'summary' => count($newDamages).' kerusakan baru terdeteksi',
                ],
                'new_damages_count' => count($newDamages),
                'estimated_cost' => round($estimatedCost, 2),
                'status' => 'completed',
                'completed_at' => now(),
            ]);
        } catch (\Throwable $e) {
            $comparison->update([
                'status' => 'failed',
                'analysis' => ['error' => str($e->getMessage())->limit(300)],
            ]);

            throw $e;
        }

        return $comparison->refresh();
    }

    protected function resolveProviderId(): ?int
    {
        $settingId = SystemSetting::get('ai_damage_provider_id');

        if ($settingId) {
            return (int) $settingId;
        }

        return Provider::where('type', 'ai')->where('is_active', true)->value('id');
    }

    /**
     * Path foto bisa berupa path storage atau URL absolut — normalisasi ke path storage.
     */
    protected function normalizePaths(array $photos): array
    {
        return collect($photos)
            ->map(function ($photo) {
                if (is_array($photo)) {
                    $photo = $photo['url'] ?? $photo['path'] ?? '';
                }

                $path = str_replace(['/storage/', config('app.url')], '', (string) $photo);
                $path = ltrim($path, '/');

                return Storage::disk('public')->exists($path) ? $path : null;
            })
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public static function comparisonPrompt(): string
    {
        return <<<'PROMPT'
Anda inspektur kendaraan profesional. Foto pertama setengah dari daftar adalah kondisi CHECKOUT
(sebelum sewa), sisanya kondisi RETURN (setelah sewa). Bandingkan kondisi kendaraan.

Identifikasi HANYA kerusakan yang TIDAK ada di foto checkout (kerusakan BARU akibat masa sewa).
Untuk setiap temuan tentukan apakah benar-benar baru.

Balas HANYA dengan JSON array tanpa teks lain. Format per temuan:
[{"location_on_vehicle":"contoh: pintu depan kiri","damage_type":"scratch|dent|crack|stain|broken|missing_part","severity":"minor|moderate|major|critical","description":"deskripsi singkat","estimated_cost_idr":150000,"is_new":true,"confidence":0.85}]

Jika tidak ada kerusakan baru, balas: []
PROMPT;
    }
}

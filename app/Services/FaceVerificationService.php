<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\FaceVerification;
use App\Models\Provider;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Face match KTP vs selfie. Format-based: provider vision dinamis
 * (openai_compatible dsb.) dikonfigurasi pemilik di admin Providers.
 */
class FaceVerificationService
{
    public function verify(Customer $customer, string $ktpPath, string $selfiePath, string $context = 'booking', ?int $providerId = null): FaceVerification
    {
        $provider = Provider::findOrFail($providerId ?? $this->resolveProviderId());

        abort_unless($provider->type === 'ai' && $provider->is_active, 422, 'Provider AI tidak aktif.');

        $record = FaceVerification::create([
            'customer_id' => $customer->id,
            'ktp_photo_url' => $ktpPath,
            'selfie_url' => $selfiePath,
            'provider_id' => $provider->id,
            'status' => 'pending',
            'context' => $context,
        ]);

        try {
            $config = $provider->config ?? [];
            $threshold = (float) ($config['match_threshold'] ?? SystemSetting::get('face_match_threshold', 0.75));

            $analysis = app(AiVisionService::class)->analyzeImages($provider, [$ktpPath, $selfiePath], self::prompt());

            $score = (float) data_get($analysis, '0.match_score', data_get($analysis, 'match_score', 0));
            $matched = $score >= $threshold;

            $record->update([
                'match_score' => round($score * 100, 2),
                'status' => $matched ? 'matched' : 'mismatch',
                'analysis' => $analysis,
                'checked_at' => now(),
            ]);

            if (!$matched) {
                app(TrustScoreAdjustmentService::class)->adjust($customer, -15, "Face verification gagal (skor {$score})");
            } else {
                app(TrustScoreAdjustmentService::class)->adjust($customer, 5, 'Face verification lolos');
            }
        } catch (\Throwable $e) {
            $record->update([
                'status' => 'failed',
                'analysis' => ['error' => str($e->getMessage())->limit(300)],
                'checked_at' => now(),
            ]);
        }

        return $record->refresh();
    }

    public static function prompt(): string
    {
        return <<<'PROMPT'
Bandingkan foto KTP dan foto selfie ini. Tentukan apakah wajah di KTP sama dengan orang di selfie.

Balas HANYA dengan JSON object tanpa teks lain:
{"match_score": 0.87, "same_person": true, "notes": "penjelasan singkat"}

match_score dalam rentang 0.00-1.00.
PROMPT;
    }

    protected function resolveProviderId(): int
    {
        $setting = SystemSetting::get('face_verification_provider_id');

        if ($setting) {
            return (int) $setting;
        }

        return (int) Provider::where('type', 'ai')->where('is_active', true)->value('id');
    }
}

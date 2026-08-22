<?php

namespace App\Services;

use App\Models\Provider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Format-based BYOK vision adapter.
 * api_format 'openai_compatible' covers OpenAI, OpenRouter, Groq, Ollama,
 * LM Studio, vLLM, dan provider lain dengan API chat/completions serupa.
 * Semua konfigurasi (base_url, model, auth) diinput pemilik aplikasi
 * lewat admin Providers — tidak ada vendor yang dikunci sistem.
 */
class AiVisionService
{
    public function analyzeImages(Provider $provider, array $imagePaths, string $prompt): array
    {
        abort_unless($provider->type === 'ai' && $provider->is_active, 422, 'Provider AI tidak aktif.');

        $config = $provider->config ?? [];
        $model = $config['model'] ?? null;

        if (blank($provider->base_url) || blank($model)) {
            throw new RuntimeException('Provider AI membutuhkan base_url dan config.model.');
        }

        $content = [['type' => 'text', 'text' => $prompt]];

        foreach ($imagePaths as $path) {
            $content[] = [
                'type' => 'image_url',
                'image_url' => ['url' => $this->toDataUrl($path)],
            ];
        }

        $headers = $provider->extra_headers ?? [];
        if (filled($provider->api_key)) {
            $authHeader = $config['auth_header'] ?? 'Authorization';
            $authScheme = $config['auth_scheme'] ?? 'Bearer';
            $headers[$authHeader] = trim("{$authScheme} {$provider->api_key}");
        }

        $url = rtrim($provider->base_url, '/');
        if (!str_ends_with($url, '/chat/completions')) {
            $url .= '/chat/completions';
        }

        $response = Http::withHeaders($headers)
            ->timeout((int) ($config['timeout_seconds'] ?? 60))
            ->asJson()
            ->post($url, [
                'model' => $model,
                'messages' => [['role' => 'user', 'content' => $content]],
                'max_tokens' => (int) ($config['max_tokens'] ?? 1200),
                'temperature' => 0.1,
            ]);

        if ($response->failed()) {
            throw new RuntimeException("AI API error (HTTP {$response->status()}): ".str($response->body())->limit(300));
        }

        $text = data_get($response->json(), 'choices.0.message.content', '');

        return $this->decodeJson($text);
    }

    public static function damagePrompt(): string
    {
        return <<<'PROMPT'
Anda inspektur kendaraan profesional. Periksa setiap foto kondisi mobil berikut.
Identifikasi semua kerusakan yang terlihat: baret/scratch, penyok/dent, retak/crack,
noda/stain, pecah/broken, atau komponen hilang.

Balas HANYA dengan JSON array tanpa teks lain. Format per temuan:
[{"location_on_vehicle":"contoh: pintu depan kiri","damage_type":"scratch|dent|crack|stain|broken|missing_part","severity":"minor|moderate|major|critical","description":"deskripsi singkat kerusakan","estimated_cost_idr":150000,"confidence":0.85}]

Jika tidak ada kerusakan terdeteksi, balas: []
PROMPT;
    }

    private function toDataUrl(string $path): string
    {
        if (str_starts_with($path, 'data:')) {
            return $path;
        }

        $disk = Storage::disk('public');

        if (!$disk->exists($path)) {
            throw new RuntimeException("Foto inspeksi tidak ditemukan: {$path}");
        }

        $mime = $disk->mimeType($path) ?: 'image/jpeg';

        return 'data:'.$mime.';base64,'.base64_encode($disk->get($path));
    }

    private function decodeJson(string $text): array
    {
        $text = trim((string) preg_replace('/^```(json)?|```$/m', '', trim($text)));

        $decoded = json_decode($text, true);

        if (is_array($decoded)) {
            return $decoded;
        }

        if (preg_match('/\[.*\]/s', $text, $m)) {
            $decoded = json_decode($m[0], true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return [['raw_text' => $text]];
    }
}

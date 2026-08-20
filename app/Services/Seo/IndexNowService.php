<?php

namespace App\Services\Seo;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IndexNowService
{
    protected int $batchSize = 50;
    protected string $cacheKey = 'indexnow_submitted_urls';
    protected int $cacheTtl = 86400;

    public function submitUrl(string $url): bool
    {
        return $this->submitUrls([$url]);
    }

    public function submitUrls(array $urls): int
    {
        $apiKey = $this->getApiKey();
        if (empty($apiKey)) {
            Log::warning('IndexNow: API key not configured');
            return 0;
        }

        $host = parse_url(config('app.url'), PHP_URL_HOST);
        $keyLocation = config('app.url') . '/indexnow-key.txt';

        $urls = $this->filterNewUrls($urls);
        if (empty($urls)) {
            return 0;
        }

        $batches = array_chunk($urls, $this->batchSize);
        $submittedCount = 0;

        foreach ($batches as $batch) {
            $payload = [
                'host' => $host,
                'key' => $apiKey,
                'keyLocation' => $keyLocation,
                'urlList' => $batch,
            ];

            try {
                $response = Http::timeout(10)
                    ->withHeaders(['Content-Type' => 'application/json'])
                    ->post((string) config('seo.indexnow_endpoint'), $payload);

                if ($response->successful() || $response->status() === 202) {
                    $submittedCount += count($batch);
                    $this->markAsSubmitted($batch);
                    Log::info('IndexNow: batch submitted', ['count' => count($batch)]);
                } else {
                    Log::warning('IndexNow: batch failed', ['status' => $response->status()]);
                }
            } catch (\Exception $e) {
                Log::error('IndexNow: HTTP error', ['message' => $e->getMessage()]);
            }
        }

        return $submittedCount;
    }

    public function ping(string $resourceType, string $identifier): bool
    {
        $baseUrl = config('app.url');
        $url = match ($resourceType) {
            'blog' => $baseUrl . '/blog/' . $identifier,
            'vehicle' => $baseUrl . '/sewa/' . $identifier,
            'category' => $baseUrl . '/sewa-mobil',
            default => $baseUrl . '/' . $identifier,
        };

        return $this->submitUrl($url);
    }

    public function getApiKey(): string
    {
        return (string) SystemSetting::get('indexnow_api_key', '');
    }

    public function generateApiKey(): string
    {
        return bin2hex(random_bytes(16));
    }

    public function saveApiKey(string $key): void
    {
        SystemSetting::set('indexnow_api_key', $key);
    }

    protected function filterNewUrls(array $urls): array
    {
        $submitted = Cache::get($this->cacheKey, []);
        return array_values(array_diff($urls, $submitted));
    }

    protected function markAsSubmitted(array $urls): void
    {
        $submitted = Cache::get($this->cacheKey, []);
        $submitted = array_merge($submitted, $urls);
        $submitted = array_unique($submitted);
        Cache::put($this->cacheKey, $submitted, $this->cacheTtl);
    }

    public function getStats(): array
    {
        $submitted = Cache::get($this->cacheKey, []);
        return [
            'api_key_configured' => !empty($this->getApiKey()),
            'cached_urls_count' => count($submitted),
        ];
    }
}

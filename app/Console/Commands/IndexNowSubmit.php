<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\BlogPost;
use App\Models\SystemSetting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IndexNowSubmit extends Command
{
    protected $signature = 'seo:indexnow';

    protected $description = 'Submit new and updated URLs to IndexNow (Bing, Yandex, Seznam, Naver)';

    protected int $batchSize = 50;

    public function handle(): int
    {
        $apiKey = SystemSetting::get('indexnow_api_key');
        $host = parse_url(config('app.url', 'https://rentalmobil.test'), PHP_URL_HOST);

        if (empty($apiKey)) {
            $this->error('IndexNow API key not configured. Set "indexnow_api_key" in SystemSetting.');
            return Command::FAILURE;
        }

        $urls = $this->collectUpdatedUrls();

        if (empty($urls)) {
            $this->info('No URLs to submit.');
            return Command::SUCCESS;
        }

        $batches = array_chunk($urls, $this->batchSize);
        $submittedCount = 0;

        foreach ($batches as $batch) {
            $payload = [
                'host' => $host,
                'key' => $apiKey,
                'keyLocation' => config('app.url', 'https://rentalmobil.test') . '/indexnow-key.txt',
                'urlList' => $batch,
            ];

            try {
                $response = Http::timeout(10)
                    ->withHeaders(['Content-Type' => 'application/json'])
                    ->post('https://api.indexnow.org/indexnow', $payload);

                if ($response->successful() || $response->status() === 202) {
                    $submittedCount += count($batch);
                    $this->line("  Submitted batch of " . count($batch) . " URL(s).");
                } else {
                    $this->warn("  Batch failed with status {$response->status()}.");
                    Log::warning('IndexNow batch failed', ['status' => $response->status(), 'body' => $response->body()]);
                }
            } catch (\Exception $e) {
                $this->error("  HTTP error: {$e->getMessage()}");
                Log::error('IndexNow HTTP error', ['message' => $e->getMessage()]);
            }
        }

        $this->info("Submitted {$submittedCount} URL(s) to IndexNow.");
        Log::info('IndexNowSubmit completed', ['submitted' => $submittedCount, 'total' => count($urls)]);

        return Command::SUCCESS;
    }

    protected function collectUpdatedUrls(): array
    {
        $baseUrl = config('app.url', 'https://rentalmobil.test');
        $urls = [];

        $recentPosts = BlogPost::published()
            ->where('updated_at', '>=', now()->subHours(48))
            ->pluck('slug')
            ->toArray();

        foreach ($recentPosts as $slug) {
            $urls[] = $baseUrl . '/blog/' . $slug;
        }

        $recentChanges = AuditLog::where('created_at', '>=', now()->subHours(48))
            ->whereIn('action', ['created', 'updated', 'status_change'])
            ->where('auditable_type', BlogPost::class)
            ->pluck('auditable_id')
            ->unique()
            ->toArray();

        foreach ($recentChanges as $id) {
            $post = BlogPost::find($id);
            if ($post && $post->is_published) {
                $url = $baseUrl . '/blog/' . $post->slug;
                if (!in_array($url, $urls)) {
                    $urls[] = $url;
                }
            }
        }

        return array_values(array_unique($urls));
    }
}

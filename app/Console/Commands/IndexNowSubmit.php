<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\BlogPost;
use App\Services\Seo\IndexNowService;
use Illuminate\Console\Command;

class IndexNowSubmit extends Command
{
    protected $signature = 'seo:indexnow';

    protected $description = 'Submit new and updated URLs to IndexNow (Bing, Yandex, Seznam, Naver)';

    public function handle(IndexNowService $indexNow): int
    {
        if (empty($indexNow->getApiKey())) {
            $this->error('IndexNow API key not configured. Set "indexnow_api_key" in SystemSetting.');
            return Command::FAILURE;
        }

        $urls = $this->collectUpdatedUrls();

        if (empty($urls)) {
            $this->info('No URLs to submit.');
            return Command::SUCCESS;
        }

        $submittedCount = $indexNow->submitUrls($urls);

        $this->info("Submitted {$submittedCount} URL(s) to IndexNow.");

        return Command::SUCCESS;
    }

    protected function collectUpdatedUrls(): array
    {
        $baseUrl = config('app.url');
        $urls = [];

        $recentPosts = BlogPost::query()
            ->where('is_published', true)
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

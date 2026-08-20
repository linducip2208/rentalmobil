<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\Category;
use App\Models\Location;
use App\Models\SitemapEntry;
use App\Models\Vehicle;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class SitemapController extends Controller
{
    private const CHUNK_SIZE = 20000;

    public function index(): Response
    {
        $xml = Cache::remember('sitemap:index', 86400, function () {
            $base = rtrim(config('app.url'), '/');
            $chunks = [['section' => 'core', 'page' => 1, 'lastmod' => now()]];
            $customPages = max(1, (int) ceil(SitemapEntry::active()->count() / self::CHUNK_SIZE));
            for ($page = 1; $page <= $customPages; $page++) $chunks[] = ['section' => 'custom', 'page' => $page, 'lastmod' => SitemapEntry::active()->max('updated_at') ?? now()];
            $body = collect($chunks)->map(fn ($chunk) => "  <sitemap>\n    <loc>".htmlspecialchars("{$base}/sitemap/{$chunk['section']}-{$chunk['page']}.xml", ENT_XML1)."</loc>\n    <lastmod>".date('c', strtotime((string) $chunk['lastmod']))."</lastmod>\n  </sitemap>")->implode("\n");
            return '<?xml version="1.0" encoding="UTF-8"?>'."\n".'<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n{$body}\n</sitemapindex>";
        });
        return $this->xml($xml);
    }

    public function show(string $section, int $page): Response
    {
        abort_unless(in_array($section, ['core', 'custom'], true) && $page > 0, 404);
        $xml = Cache::remember("sitemap:{$section}:{$page}", 86400, fn () => $this->buildChunk($section, $page));
        abort_if($xml === null, 404);
        return $this->xml($xml);
    }

    private function buildChunk(string $section, int $page): ?string
    {
        $base = rtrim(config('app.url'), '/'); $entries = [];
        if ($section === 'core') {
            if ($page !== 1) return null;
            foreach (['/' => ['weekly','.80'], '/docs' => ['monthly','.60'], '/blog' => ['weekly','.70'], '/faq' => ['monthly','.60'], '/contact' => ['monthly','.50'], '/sewa-mobil' => ['weekly','.80']] as $path => [$freq,$priority]) $entries[] = [$base.$path,$freq,$priority,null];
            Location::active()->whereNotNull('city')->distinct()->pluck('city')->each(fn ($city) => $entries[] = [$base.'/sewa-mobil-di-'.Str::slug($city),'weekly','.70',null]);
            $vehicles = Vehicle::where('is_active', true)->get();
            $vehicles->each(fn ($vehicle) => $entries[] = [$base.'/sewa/'.$vehicle->slug,'weekly','.90',$vehicle->updated_at]);
            BlogPost::published()->get()->each(fn ($post) => $entries[] = [$base.'/blog/'.$post->slug,'monthly','.60',$post->updated_at]);
            Category::active()->get()->each(function ($category) use (&$entries, $base) { $slug=Str::slug($category->name); $entries[] = [$base.'/best-'.$slug,'monthly','.65',$category->updated_at]; $entries[] = [$base.'/best-'.$slug.'-'.now()->year,'monthly','.65',$category->updated_at]; });
            $vehicles->take(250)->each(function ($a) use ($vehicles, &$entries, $base) { $vehicles->where('id','>',$a->id)->take(4)->each(fn ($b) => $entries[] = [$base.'/compare/'.$a->slug.'-vs-'.$b->slug,'monthly','.60',max($a->updated_at,$b->updated_at)]); });
        } else {
            $rows = SitemapEntry::active()->orderBy('id')->forPage($page, self::CHUNK_SIZE)->get();
            if ($rows->isEmpty() && $page > 1) return null;
            foreach ($rows as $row) $entries[] = [$base.'/'.ltrim($row->url,'/'),$row->change_frequency ?? 'monthly',(string)($row->priority ?? '.50'),$row->last_mod];
        }
        $body = collect($entries)->map(fn ($entry) => $this->urlEntry(...$entry))->implode('');
        return '<?xml version="1.0" encoding="UTF-8"?>'."\n".'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n{$body}</urlset>";
    }

    private function urlEntry(string $loc, string $frequency, string $priority, mixed $lastmod): string
    {
        $modified = $lastmod ? "\n    <lastmod>".date('c', strtotime((string) $lastmod)).'</lastmod>' : '';
        return "  <url>\n    <loc>".htmlspecialchars($loc, ENT_XML1)."</loc>{$modified}\n    <changefreq>{$frequency}</changefreq>\n    <priority>{$priority}</priority>\n  </url>\n";
    }

    private function xml(string $xml): Response { return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8', 'X-Robots-Tag' => 'noindex']); }
}

<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\Category;
use App\Models\Faq;
use App\Models\SitemapEntry;
use App\Models\Vehicle;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $xml = Cache::remember('sitemap_xml', 86400, fn () => $this->buildSitemap());

        return response($xml, 200, [
            'Content-Type' => 'application/xml',
            'X-Robots-Tag' => 'noindex',
        ]);
    }

    protected function buildSitemap(): string
    {
        $url = config('app.url', 'https://rentalmobil.test');
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        $staticPages = [
            '/' => ['freq' => 'weekly', 'priority' => '0.80'],
            '/blog' => ['freq' => 'weekly', 'priority' => '0.70'],
            '/faq' => ['freq' => 'monthly', 'priority' => '0.60'],
            '/contact' => ['freq' => 'monthly', 'priority' => '0.50'],
            '/docs' => ['freq' => 'monthly', 'priority' => '0.60'],
        ];

        foreach ($staticPages as $page => $meta) {
            $xml .= $this->urlEntry($url . $page, $meta['freq'], $meta['priority']);
        }

        $categories = Category::active()->get();
        foreach ($categories as $category) {
            $xml .= $this->urlEntry(
                $url . '/sewa-mobil-di-' . str_replace(' ', '-', strtolower($category->name)),
                'weekly',
                '0.70',
                $category->updated_at
            );
        }

        $xml .= $this->urlEntry($url . '/sewa-mobil', 'weekly', '0.80');

        $vehicles = Vehicle::where('is_active', true)->get();
        foreach ($vehicles as $vehicle) {
            $xml .= $this->urlEntry($url . '/sewa/' . $vehicle->slug, 'weekly', '0.90', $vehicle->updated_at);
        }

        $posts = BlogPost::published()->get();
        foreach ($posts as $post) {
            $xml .= $this->urlEntry($url . '/blog/' . $post->slug, 'monthly', '0.60', $post->updated_at);
        }

        $faqs = Faq::active()->pluck('question', 'id');
        if ($faqs->isNotEmpty()) {
            $xml .= $this->urlEntry($url . '/faq', 'monthly', '0.60');
        }

        foreach ($categories as $cat) {
            $xml .= $this->urlEntry(
                $url . '/best-' . str_replace(' ', '-', strtolower($cat->name)),
                'monthly',
                '0.65',
                $cat->updated_at
            );
        }

        $entries = SitemapEntry::active()->get();
        foreach ($entries as $entry) {
            $xml .= $this->urlEntry(
                $url . $entry->url,
                $entry->change_frequency ?? 'monthly',
                $entry->priority ?? '0.50',
                $entry->last_modified
            );
        }

        $xml .= '</urlset>';
        return $xml;
    }

    protected function urlEntry(string $loc, string $changefreq = 'monthly', string $priority = '0.50', $lastmod = null): string
    {
        $lastmodStr = '';
        if ($lastmod) {
            $lastmodStr = "\n    <lastmod>" . $lastmod->format('Y-m-d\TH:i:sP') . "</lastmod>";
        }

        return sprintf(
            "  <url>\n    <loc>%s</loc>%s\n    <changefreq>%s</changefreq>\n    <priority>%s</priority>\n  </url>\n",
            htmlspecialchars($loc, ENT_XML1),
            $lastmodStr,
            $changefreq,
            $priority
        );
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\Category;
use App\Models\SitemapEntry;
use App\Models\Vehicle;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $xml = $this->buildSitemap();

        return response($xml, 200, [
            'Content-Type' => 'application/xml',
        ]);
    }

    protected function buildSitemap(): string
    {
        $url = config('app.url', 'https://rentalmobil.test');
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        $staticPages = ['/', '/blog', '/faq', '/contact', '/docs'];
        foreach ($staticPages as $page) {
            $xml .= $this->urlEntry($url . $page, 'weekly', '0.80');
        }

        $categories = Category::active()->get();
        foreach ($categories as $category) {
            $xml .= $this->urlEntry($url . '/kategori/' . $category->slug, 'weekly', '0.70');
        }

        $vehicles = Vehicle::available()->active()->get();
        foreach ($vehicles as $vehicle) {
            $xml .= $this->urlEntry($url . '/mobil/' . $vehicle->slug, 'weekly', '0.90');
        }

        $posts = BlogPost::published()->get();
        foreach ($posts as $post) {
            $xml .= $this->urlEntry($url . '/blog/' . $post->slug, 'monthly', '0.60');
        }

        $entries = SitemapEntry::active()->get();
        foreach ($entries as $entry) {
            $xml .= $this->urlEntry(
                $url . $entry->url,
                $entry->change_frequency ?? 'monthly',
                $entry->priority ?? '0.50'
            );
        }

        $xml .= '</urlset>';
        return $xml;
    }

    protected function urlEntry(string $loc, string $changefreq = 'monthly', string $priority = '0.50'): string
    {
        return sprintf(
            "  <url>\n    <loc>%s</loc>\n    <changefreq>%s</changefreq>\n    <priority>%s</priority>\n  </url>\n",
            htmlspecialchars($loc, ENT_XML1),
            $changefreq,
            $priority
        );
    }
}

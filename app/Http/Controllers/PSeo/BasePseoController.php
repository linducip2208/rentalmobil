<?php

namespace App\Http\Controllers\PSeo;

use App\Http\Controllers\Controller;

class BasePseoController extends Controller
{
    protected function seoMeta(string $title, string $description, ?string $canonical = null, ?string $jsonLd = null): array
    {
        return [
            'seoTitle' => $title,
            'seoDescription' => $description,
            'seoCanonical' => $canonical ?? url()->current(),
            'seoJsonLd' => $jsonLd,
        ];
    }

    protected function jsonLdItemList(array $items, string $name, string $url): string
    {
        $listItems = array_map(function ($item, $pos) {
            return [
                '@type' => 'ListItem',
                'position' => $pos + 1,
                'name' => $item['name'] ?? $item,
                'url' => $item['url'] ?? url('/sewa/' . ($item['slug'] ?? '')),
            ];
        }, $items, array_keys($items));

        return json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'name' => $name,
            'url' => $url,
            'itemListElement' => $listItems,
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    protected function jsonLdProduct(array $data): string
    {
        return json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $data['name'] ?? '',
            'description' => $data['description'] ?? '',
            'image' => $data['image'] ?? '',
            'brand' => [
                '@type' => 'Brand',
                'name' => $data['brand'] ?? 'RentalMobil',
            ],
            'offers' => [
                '@type' => 'Offer',
                'price' => $data['price'] ?? 0,
                'priceCurrency' => 'IDR',
                'availability' => 'https://schema.org/InStock',
                'url' => $data['url'] ?? url()->current(),
            ],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    protected function jsonLdLocalBusiness(string $city): string
    {
        return json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'LocalBusiness',
            'name' => "RentalMobil {$city}",
            'description' => "Sewa mobil terpercaya di {$city}",
            'address' => [
                '@type' => 'PostalAddress',
                'addressLocality' => $city,
                'addressCountry' => 'ID',
            ],
            'url' => url("/sewa-mobil-di-" . str_replace(' ', '-', strtolower($city))),
            'telephone' => '+6281234567890',
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    protected function jsonLdFaqPage(array $faqs): string
    {
        $entities = array_map(function ($faq) {
            return [
                '@type' => 'Question',
                'name' => $faq['q'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $faq['a'],
                ],
            ];
        }, $faqs);

        return json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $entities,
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    protected function jsonLdArticle(array $data): string
    {
        return json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $data['title'] ?? '',
            'description' => $data['description'] ?? '',
            'author' => [
                '@type' => 'Organization',
                'name' => $data['author'] ?? 'RentalMobil',
            ],
            'datePublished' => $data['datePublished'] ?? now()->toIso8601String(),
            'publisher' => [
                '@type' => 'Organization',
                'name' => 'RentalMobil',
            ],
            'image' => $data['image'] ?? '',
            'url' => $data['url'] ?? url()->current(),
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}

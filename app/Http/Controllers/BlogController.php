<?php

namespace App\Http\Controllers;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $query = BlogPost::published()->with('category');

        if ($request->filled('category')) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $request->category));
        }

        $posts = $query->latest('published_at')->paginate(9);
        $categories = BlogCategory::active()->withCount('publishedPosts')->get();

        return view('blog.index', compact('posts', 'categories'));
    }

    public function show(string $slug)
    {
        $post = BlogPost::published()
            ->with(['category', 'author'])
            ->where('slug', $slug)
            ->firstOrFail();

        $post->increment('views');

        $related = BlogPost::published()
            ->where('category_id', $post->category_id)
            ->where('id', '!=', $post->id)
            ->take(3)
            ->get();

        $jsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $post->meta_title ?? $post->title,
            'description' => $post->meta_description ?? $post->excerpt,
            'author' => [
                '@type' => 'Organization',
                'name' => 'RentalMobil',
            ],
            'datePublished' => $post->published_at->toIso8601String(),
            'dateModified' => $post->updated_at->toIso8601String(),
            'publisher' => [
                '@type' => 'Organization',
                'name' => 'RentalMobil',
            ],
            'image' => $post->featured_image,
            'url' => url('/blog/'.$post->slug),
        ];

        return view('blog.show', [
            'post' => $post,
            'related' => $related,
            'seoTitle' => $post->meta_title ?? $post->title.' | RentalMobil',
            'seoDescription' => $post->meta_description ?? $post->excerpt,
            'seoCanonical' => url('/blog/'.$post->slug),
            'seoJsonLd' => json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        ]);
    }

    public function feed()
    {
        $posts = BlogPost::published()
            ->latest('published_at')
            ->limit(20)
            ->get();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">'."\n";
        $xml .= '<channel>'."\n";
        $xml .= '  <title>RentalMobil Blog</title>'."\n";
        $xml .= '  <link>'.htmlspecialchars(config('app.url', 'https://rentalmobil.test')).'</link>'."\n";
        $xml .= '  <description>Artikel tips dan panduan seputar rental mobil</description>'."\n";
        $xml .= '  <language>id-ID</language>'."\n";
        $xml .= '  <atom:link href="'.htmlspecialchars(url('/blog/feed.xml')).'" rel="self" type="application/rss+xml"/>'."\n";

        foreach ($posts as $post) {
            $xml .= '  <item>'."\n";
            $xml .= '    <title>'.htmlspecialchars($post->title).'</title>'."\n";
            $xml .= '    <link>'.htmlspecialchars(url('/blog/'.$post->slug)).'</link>'."\n";
            $xml .= '    <guid isPermaLink="true">'.htmlspecialchars(url('/blog/'.$post->slug)).'</guid>'."\n";
            $xml .= '    <description>'.htmlspecialchars($post->excerpt ?? strip_tags($post->content)).'</description>'."\n";
            $xml .= '    <pubDate>'.$post->published_at->toRfc2822String().'</pubDate>'."\n";
            if ($post->category) {
                $xml .= '    <category>'.htmlspecialchars($post->category->name).'</category>'."\n";
            }
            $xml .= '  </item>'."\n";
        }

        $xml .= '</channel>'."\n";
        $xml .= '</rss>';

        return response($xml, 200, [
            'Content-Type' => 'application/rss+xml',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}

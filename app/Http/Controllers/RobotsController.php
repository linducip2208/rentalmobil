<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class RobotsController extends Controller
{
    public function index(): Response
    {
        $content = <<<'ROBOTS'
User-agent: *
Allow: /$
Allow: /docs
Allow: /marketing/
Allow: /blog
Allow: /faq
Allow: /contact
Allow: /sewa-mobil
Allow: /sewa-mobil-di-*
Allow: /sewa/*
Allow: /best-*
Allow: /alternatives-to-*
Allow: /bandingkan/*
Allow: /compare/*
Disallow: /admin
Disallow: /api
Disallow: /__pair
Disallow: /webhooks

Sitemap: /sitemap.xml
ROBOTS;

        return response($content, 200, [
            'Content-Type' => 'text/plain',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}

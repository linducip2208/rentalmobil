<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Models\Page;
use App\Models\Testimonial;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Cache;

class CmsPageController extends Controller
{
    public function home()
    {
        if (auth()->check()) {
            return redirect('/admin');
        }

        $page = $this->findPublished('home');

        return $page ? $this->render($page) : response()->view('marketing.home');
    }

    public function show(string $pageSlug)
    {
        return $this->render($this->findPublished($pageSlug) ?? abort(404));
    }

    private function findPublished(string $slug): ?Page
    {
        return Cache::remember("cms.page.{$slug}", now()->addHour(), fn () => Page::published()
            ->with(['sections' => fn ($query) => $query->where('is_visible', true), 'seoMeta'])
            ->where('slug', $slug)
            ->first());
    }

    private function render(Page $page)
    {
        $blockTypes = $page->sections->pluck('block_type');

        return response()->view('cms.page', [
            'page' => $page,
            'vehicles' => $blockTypes->contains('vehicle_list')
                ? Vehicle::query()->with(['brand', 'category'])->where('is_active', true)->where('status', 'available')->limit(12)->get()
                : collect(),
            'faqs' => $blockTypes->contains('faq')
                ? Faq::query()->where('is_active', true)->orderBy('sort_order')->limit(12)->get()
                : collect(),
            'testimonials' => $blockTypes->contains('testimonial')
                ? Testimonial::query()->where('is_active', true)->orderByDesc('rating')->latest()->limit(9)->get()
                : collect(),
        ]);
    }
}

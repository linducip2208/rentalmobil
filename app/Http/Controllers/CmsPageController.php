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
        // The storefront homepage is the primary customer experience; CMS pages
        // remain available for other slugs and via /{pageSlug} routing.
        return app(StorefrontController::class)->home();
    }

    public function show(string $pageSlug)
    {
        return $this->render($this->findPublished($pageSlug) ?? abort(404));
    }

    public function corporate()
    {
        $page = $this->findPublished('corporate');

        return $page ? $this->render($page) : response()->view('marketing.corporate');
    }

    public function about()
    {
        $page = $this->findPublished('tentang-kami');

        return $page ? $this->render($page) : response()->view('marketing.about');
    }

    private function findPublished(string $slug): ?Page
    {
        // Cache only a scalar ID. Caching an Eloquent model serializes its
        // class definition and can produce __PHP_Incomplete_Class after a
        // deployment or long-running worker reload.
        $pageId = Cache::remember("cms.page.id.{$slug}", now()->addHour(), fn () => Page::published()
            ->where('slug', $slug)
            ->value('id'));

        if (! $pageId) {
            return null;
        }

        return Page::published()
            ->with(['sections' => fn ($query) => $query->where('is_visible', true), 'seoMeta'])
            ->find($pageId);
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

<?php

namespace Tests\Feature;

use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CmsPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_page_renders_blocks_and_sanitizes_custom_html(): void
    {
        $page = Page::create([
            'title' => 'Tentang Kami',
            'slug' => 'tentang-kami',
            'status' => 'published',
            'publish_at' => now(),
        ]);
        $section = $page->sections()->create([
            'block_type' => 'custom_html',
            'name' => 'Konten Aman',
            'data' => ['html' => '<h2>Tentang RentalMobil</h2><script>alert(1)</script><a href="javascript:alert(2)">Bahaya</a>'],
            'sort_order' => 1,
            'is_visible' => true,
        ]);

        $this->assertStringNotContainsString('<script', $section->fresh()->data['html']);
        $this->assertStringNotContainsString('javascript:', $section->fresh()->data['html']);

        $this->get('/tentang-kami')
            ->assertOk()
            ->assertSee('Tentang RentalMobil')
            ->assertDontSee('alert(1)', false)
            ->assertDontSee('javascript:', false);
    }

    public function test_draft_page_is_not_public(): void
    {
        Page::create(['title' => 'Draft Rahasia', 'slug' => 'draft-rahasia', 'status' => 'draft']);

        $this->get('/draft-rahasia')->assertNotFound();
    }
}

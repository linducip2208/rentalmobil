<?php

namespace App\Models;

use App\Services\CmsBlockRenderer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageSection extends Model
{
    protected $fillable = ['page_id', 'block_type', 'name', 'data', 'sort_order', 'is_visible'];

    protected function casts(): array
    {
        return ['data' => 'array', 'is_visible' => 'boolean', 'sort_order' => 'integer'];
    }

    protected static function booted(): void
    {
        static::saving(function (PageSection $section): void {
            if (in_array($section->block_type, ['rich_text', 'custom_html'], true) && isset($section->data['html'])) {
                $data = $section->data;
                $data['html'] = app(CmsBlockRenderer::class)->sanitizeHtml((string) $data['html']);
                $section->data = $data;
            }
        });
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class BlogPost extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'excerpt',
        'featured_image',
        'category_id',
        'author_id',
        'is_published',
        'published_at',
        'meta_title',
        'meta_description',
        'views',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'published_at' => 'datetime',
            'views' => 'integer',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (BlogPost $model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->title);
            }
        });
        static::updating(function (BlogPost $model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->title);
            }
        });
        static::saved(function (BlogPost $model) {
            Cache::forget('sitemap_xml');
            if ($model->isPublished()) {
                app(\App\Services\Seo\IndexNowService::class)->ping('blog', $model->slug);
            }
        });
        static::deleted(fn () => Cache::forget('sitemap_xml'));
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class, 'category_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true)
            ->where('published_at', '<=', now());
    }

    public function scopeDraft($query)
    {
        return $query->where('is_published', false);
    }

    public function scopeByCategory($query, int $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function isPublished(): bool
    {
        return $this->is_published && $this->published_at && $this->published_at->lte(now());
    }
}

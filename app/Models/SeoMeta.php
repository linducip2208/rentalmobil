<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SeoMeta extends Model
{
    protected $table = 'seo_meta';

    protected $fillable = ['meta_title', 'meta_description', 'canonical_url', 'og_title', 'og_description', 'og_image', 'is_indexable', 'is_followable', 'schema_json'];

    protected function casts(): array
    {
        return ['is_indexable' => 'boolean', 'is_followable' => 'boolean', 'schema_json' => 'array'];
    }

    public function seoable(): MorphTo
    {
        return $this->morphTo();
    }
}

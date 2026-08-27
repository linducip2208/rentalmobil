<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExternalReview extends Model
{
    protected $fillable = [
        'platform',
        'external_id',
        'author_name',
        'rating',
        'content',
        'review_date',
        'is_featured',
        'import_batch',
        'imported_by',
    ];


    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'review_date' => 'date',
            'is_featured' => 'boolean',
        ];
    }

    public function importedBy(): BelongsTo { return $this->belongsTo(User::class, 'imported_by'); }
}

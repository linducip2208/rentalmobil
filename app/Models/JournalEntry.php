<?php

namespace App\Models;

use App\Services\PeriodClosingService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JournalEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'entry_number',
        'posting_key',
        'location_id',
        'date',
        'description',
        'reference_type',
        'reference_id',
        'total_debit',
        'total_credit',
        'status',
        'posted_by',
        'posted_at',
        'reversed_at',
        'reversal_entry_id',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'total_debit' => 'decimal:2',
            'total_credit' => 'decimal:2',
            'posted_at' => 'datetime',
            'reversed_at' => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (JournalEntry $model) {
            app(PeriodClosingService::class)->assertPostingAllowed($model->date);
            if ($model->status === 'posted' && abs((float) $model->total_debit - (float) $model->total_credit) > 0.01) {
                throw new \RuntimeException('Jurnal tidak seimbang dan tidak dapat diposting.');
            }
            if (empty($model->entry_number)) {
                $model->entry_number = static::generateEntryNumber();
            }
            if (! $model->posted_at) {
                $model->posted_at = now();
            }
        });
    }

    public static function generateEntryNumber(): string
    {
        $prefix = 'JE';
        $date = now()->format('ymd');
        $last = static::where('entry_number', 'like', "{$prefix}{$date}%")
            ->orderByDesc('entry_number')
            ->value('entry_number');

        if ($last) {
            $sequence = (int) substr($last, -4) + 1;
        } else {
            $sequence = 1;
        }

        return $prefix.$date.str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(JournalLine::class, 'journal_entry_id');
    }

    public function postedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function reversalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'reversal_entry_id');
    }

    public function reversedEntries(): HasMany
    {
        return $this->hasMany(JournalEntry::class, 'reversal_entry_id');
    }

    public function scopePosted($query)
    {
        return $query->where('status', 'posted');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeReversed($query)
    {
        return $query->where('status', 'reversed');
    }

    public function isBalanced(): bool
    {
        return round($this->total_debit, 2) === round($this->total_credit, 2);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankStatementLine extends Model
{
    protected $fillable = [
        'import_id',
        'transaction_date',
        'description',
        'amount_in',
        'amount_out',
        'reference',
        'match_status',
        'matched_payment_id',
        'match_confidence',
        'match_note',
    ];

    protected function casts(): array
    {
        return [
            'transaction_date' => 'date',
            'amount_in' => 'decimal:2',
            'amount_out' => 'decimal:2',
            'match_confidence' => 'decimal:2',
        ];
    }

    public function import(): BelongsTo
    {
        return $this->belongsTo(BankStatementImport::class, 'import_id');
    }

    public function matchedPayment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'matched_payment_id');
    }

    public function lineAmount(): float
    {
        return (float) $this->amount_in - (float) $this->amount_out;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class SecureToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'token_hash',
        'scope',
        'reference_type',
        'reference_id',
        'expires_at',
        'revoked_at',
        'created_by',
        'metadata',
    ];

    protected $hidden = [
        'token_hash',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'revoked_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (SecureToken $model) {
            if (empty($model->token_hash)) {
                $raw = Str::random(64);
                $model->token_hash = hash('sha256', $raw);
                $model->raw_token = $raw;
            }
        });
    }

    public $raw_token;

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reference()
    {
        return $this->morphTo('reference');
    }

    public function scopeValid($query)
    {
        return $query->whereNull('revoked_at')
            ->where('expires_at', '>', now());
    }

    public function scopeByScope($query, string $scope)
    {
        return $query->where('scope', $scope);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isRevoked(): bool
    {
        return ! is_null($this->revoked_at);
    }

    public function isValid(): bool
    {
        return ! $this->isRevoked() && ! $this->isExpired();
    }

    public function revoke(): void
    {
        $this->update(['revoked_at' => now()]);
    }

    public static function generateToken(string $scope, string $referenceType, int $referenceId, int $expiresInMinutes = 60, ?int $createdBy = null): self
    {
        $raw = Str::random(64);

        return static::create([
            'token_hash' => hash('sha256', $raw),
            'scope' => $scope,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'expires_at' => now()->addMinutes($expiresInMinutes),
            'created_by' => $createdBy,
        ])->tap(fn ($token) => $token->raw_token = $raw);
    }
}

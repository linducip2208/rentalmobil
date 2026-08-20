<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Crypt;

class GpsIntegration extends Model
{
    protected $fillable = [
        'provider_id', 'adapter_format', 'auth_type', 'credential_key_name', 'credential_secret',
        'devices_endpoint', 'positions_endpoint', 'events_endpoint', 'commands_endpoint', 'http_method',
        'request_parameters', 'field_mapping', 'response_paths', 'webhook_identifier_field', 'webhook_secret',
        'webhook_signature_header',
        'poll_interval_minutes', 'last_synced_at', 'last_success_at', 'last_error', 'failure_count',
        'health_checked_at', 'health_status', 'is_active',
    ];

    protected $hidden = ['credential_secret_encrypted', 'webhook_secret_encrypted'];

    protected function casts(): array
    {
        return [
            'request_parameters' => 'array', 'field_mapping' => 'array', 'response_paths' => 'array',
            'last_synced_at' => 'datetime', 'last_success_at' => 'datetime', 'health_checked_at' => 'datetime', 'is_active' => 'boolean',
        ];
    }

    public function provider(): BelongsTo { return $this->belongsTo(Provider::class); }
    public function trackers(): HasMany { return $this->hasMany(GpsTracker::class); }

    public function setCredentialSecretAttribute(?string $value): void
    {
        if (filled($value)) $this->attributes['credential_secret_encrypted'] = Crypt::encryptString($value);
    }

    public function getCredentialSecretAttribute(): ?string
    {
        return $this->decrypt($this->credential_secret_encrypted);
    }

    public function setWebhookSecretAttribute(?string $value): void
    {
        if (filled($value)) $this->attributes['webhook_secret_encrypted'] = Crypt::encryptString($value);
    }

    public function getWebhookSecretAttribute(): ?string
    {
        return $this->decrypt($this->webhook_secret_encrypted);
    }

    private function decrypt(?string $value): ?string
    {
        if (blank($value)) return null;
        try { return Crypt::decryptString($value); } catch (\Throwable) { return null; }
    }

    public function scopeActive($query) { return $query->where('is_active', true); }
    public function isDue(): bool { return !$this->last_synced_at || $this->last_synced_at->lte(now()->subMinutes($this->poll_interval_minutes)); }
}

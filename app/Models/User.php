<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'name',
        'location_id',
        'email',
        'password',
        'phone',
        'avatar',
        'role',
        'is_active',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(RentalOrder::class, 'driver_id', 'id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function managedOrders(): HasMany
    {
        return $this->hasMany(RentalOrder::class, 'created_by');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function approvedExpenses(): HasMany
    {
        return $this->hasMany(Expense::class, 'approved_by');
    }

    public function maintenanceLogs(): HasMany
    {
        return $this->hasMany(MaintenanceLog::class, 'performed_by');
    }

    public function fuelLogs(): HasMany
    {
        return $this->hasMany(FuelLog::class, 'logged_by');
    }

    public function kmLogs(): HasMany
    {
        return $this->hasMany(KmLog::class, 'logged_by');
    }

    public function verifiedPayments(): HasMany
    {
        return $this->hasMany(Payment::class, 'verified_by');
    }

    public function journalEntries(): HasMany
    {
        return $this->hasMany(JournalEntry::class, 'posted_by');
    }

    public function transfers(): HasMany
    {
        return $this->hasMany(Transfer::class, 'created_by');
    }

    public function blacklistEntries(): HasMany
    {
        return $this->hasMany(BlacklistEntry::class, 'added_by');
    }

    public function trustScoreLogs(): HasMany
    {
        return $this->hasMany(TrustScoreLog::class, 'changed_by');
    }

    public function watchLists(): HasMany
    {
        return $this->hasMany(WatchList::class, 'added_by');
    }

    public function resolvedWatchLists(): HasMany
    {
        return $this->hasMany(WatchList::class, 'resolved_by');
    }

    public function investigationCases(): HasMany
    {
        return $this->hasMany(InvestigationCase::class, 'assigned_to');
    }

    public function blogPosts(): HasMany
    {
        return $this->hasMany(BlogPost::class, 'author_id');
    }

    public function auditLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'user');
    }

    public function returnInspections(): HasMany
    {
        return $this->hasMany(ReturnRecord::class, 'inspector_id');
    }

    public function approvedReturns(): HasMany
    {
        return $this->hasMany(ReturnRecord::class, 'approved_by');
    }

    public function deliveredOrders(): HasMany
    {
        return $this->hasMany(Delivery::class, 'driver_id');
    }

    public function damageReports(): HasMany
    {
        return $this->hasMany(DamageReport::class, 'reported_by');
    }

    public function assessedDamages(): HasMany
    {
        return $this->hasMany(DamageReport::class, 'assessed_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDrivers($query)
    {
        return $query->where('role', 'driver');
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'super_admin']);
    }

    public function isDriver(): bool
    {
        return $this->role === 'driver';
    }

    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_active
            && in_array($this->role, ['super_admin', 'owner', 'admin', 'manager', 'finance', 'cashier', 'driver', 'mechanic'], true);
    }
}

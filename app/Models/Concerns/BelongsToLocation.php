<?php

namespace App\Models\Concerns;

use App\Models\Location;
use Illuminate\Database\Eloquent\Builder;

trait BelongsToLocation
{
    protected static function bootBelongsToLocation(): void
    {
        static::addGlobalScope('user_location', function (Builder $q) {
            $user = auth()->user();
            $global = $user && (in_array($user->role ?? null, ['super_admin', 'owner'], true) || (method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['Super Admin', 'Owner'])));
            if ($user?->location_id && ! $global) {
                $q->where($q->qualifyColumn('location_id'), $user->location_id);
            }
        });
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}

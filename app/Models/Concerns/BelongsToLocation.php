<?php
namespace App\Models\Concerns;
use Illuminate\Database\Eloquent\Builder;
trait BelongsToLocation {protected static function bootBelongsToLocation():void{static::addGlobalScope('user_location',function(Builder $q){$user=auth()->user();if($user?->location_id)$q->where($q->qualifyColumn('location_id'),$user->location_id);});}public function location(){return $this->belongsTo(\App\Models\Location::class);}}

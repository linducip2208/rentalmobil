<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class GpsGeofence extends Model { protected $fillable=['location_id','name','type','geometry','is_active']; protected function casts():array{return['geometry'=>'array','is_active'=>'boolean'];} public function location(){return $this->belongsTo(Location::class);} }

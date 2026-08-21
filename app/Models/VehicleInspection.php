<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class VehicleInspection extends Model { protected $fillable=['rental_order_id','vehicle_id','inspector_id','type','checklist','photos','geo','customer_signature','staff_signature','result','inspected_at']; protected function casts():array{return['checklist'=>'array','photos'=>'array','geo'=>'array','inspected_at'=>'datetime'];} public function rentalOrder(){return $this->belongsTo(RentalOrder::class);} public function vehicle(){return $this->belongsTo(Vehicle::class);} public function inspector(){return $this->belongsTo(User::class);} }

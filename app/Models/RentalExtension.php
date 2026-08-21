<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class RentalExtension extends Model { protected $fillable=['rental_order_id','customer_id','requested_end_date','additional_amount','status','reviewed_by','reviewed_at','reason']; protected function casts():array{return['requested_end_date'=>'date','additional_amount'=>'decimal:2','reviewed_at'=>'datetime'];} public function rentalOrder(){return $this->belongsTo(RentalOrder::class);} public function customer(){return $this->belongsTo(Customer::class);} public function reviewer(){return $this->belongsTo(User::class,'reviewed_by');} }

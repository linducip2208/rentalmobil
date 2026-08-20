<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class BookingWaitlist extends Model
{
    protected $fillable = ['customer_id','category_id','location_id','start_date','end_date','priority','status','offered_at','expires_at','converted_booking_id','notes'];
    protected function casts(): array { return ['start_date'=>'date','end_date'=>'date','offered_at'=>'datetime','expires_at'=>'datetime']; }
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function category(): BelongsTo { return $this->belongsTo(Category::class); }
    public function location(): BelongsTo { return $this->belongsTo(Location::class); }
    public function convertedBooking(): BelongsTo { return $this->belongsTo(Booking::class, 'converted_booking_id'); }
}

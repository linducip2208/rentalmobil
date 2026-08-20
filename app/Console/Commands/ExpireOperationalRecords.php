<?php
namespace App\Console\Commands;
use App\Models\BookingWaitlist; use App\Models\CustomerDocument; use Illuminate\Console\Command;
class ExpireOperationalRecords extends Command
{
    protected $signature='rental:expire-operational-records'; protected $description='Tandai dokumen pelanggan dan penawaran waitlist yang kedaluwarsa';
    public function handle():int { CustomerDocument::whereNotNull('expiry_date')->where('expiry_date','<',today())->whereNotIn('status',['expired','rejected'])->update(['status'=>'expired']); BookingWaitlist::where('status','offered')->where('expires_at','<',now())->update(['status'=>'expired']); return self::SUCCESS; }
}

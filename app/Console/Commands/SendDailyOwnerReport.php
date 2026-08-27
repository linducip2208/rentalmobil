<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\RentalOrder;
use App\Models\User;
use App\Services\NotificationDispatcher;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendDailyOwnerReport extends Command
{
    protected $signature = 'report:daily-owner {--date=}';

    protected $description = 'Kirim ringkasan harian (pendapatan, booking, okupansi) ke owner/admin via channel notifikasi aktif';

    public function handle(NotificationDispatcher $dispatcher): int
    {
        $date = $this->option('date') ? Carbon::parse($this->option('date')) : now()->subDay();

        $newBookings = RentalOrder::whereDate('created_at', $date->toDateString())->count();
        $returns = RentalOrder::whereDate('actual_return_date', $date->toDateString())->whereIn('status', ['completed'])->count();
        $overdue = RentalOrder::overdue()->count();

        $revenueToday = Invoice::query()
            ->join('payments', 'payments.invoice_id', '=', 'invoices.id')
            ->where('payments.status', 'verified')
            ->whereDate('payments.payment_date', $date->toDateString())
            ->sum('payments.amount');

        $recipients = User::whereIn('role', ['owner', 'admin'])->where('is_active', true)->get();
        $sent = 0;

        foreach ($recipients as $user) {
            if (! $user->phone && ! $user->email) {
                continue;
            }

            if ($dispatcher->dispatch('daily_owner_report', $user, [
                'report_date' => $date->format('d M Y'),
                'new_bookings' => $newBookings,
                'returns_completed' => $returns,
                'revenue_verified' => number_format((float) $revenueToday, 0, ',', '.'),
                'overdue_orders' => $overdue,
            ])) {
                $sent++;
            }
        }

        $this->info("Laporan harian {$date->toDateString()} dikirim ke {$sent} penerima.");

        return self::SUCCESS;
    }
}

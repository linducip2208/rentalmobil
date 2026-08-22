<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\PromoVoucher;
use App\Models\RentalOrder;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GenerateMarketingVouchers extends Command
{
    protected $signature = 'marketing:generate-vouchers';

    protected $description = 'Buat voucher win-back (customer tidak aktif 90+ hari) & birthday voucher otomatis, idempoten per hari';

    public function handle(): int
    {
        $created = 0;
        $created += $this->winBackVouchers();
        $created += $this->birthdayVouchers();

        $this->info("{$created} voucher dibuat.");

        return self::SUCCESS;
    }

    private function winBackVouchers(): int
    {
        $cutoff = now()->subDays(90);
        $count = 0;

        Customer::active()->verified()->chunkById(500, function ($customers) use ($cutoff, &$count) {
            foreach ($customers as $customer) {
                $lastOrder = RentalOrder::where('customer_id', $customer->id)
                    ->whereNotIn('status', ['cancelled'])
                    ->latest('start_date')
                    ->first();

                // Hanya customer yang PERNAH sewa lalu diam 90+ hari.
                if (!$lastOrder || $lastOrder->start_date->gt($cutoff)) {
                    continue;
                }

                if ($this->voucherExists("WINBACK-{$customer->id}-".now()->format('Ym'))) {
                    continue;
                }

                PromoVoucher::create($this->voucherData(
                    "WINBACK-{$customer->id}-".now()->format('Ym'),
                    'Win-back '.$customer->name,
                    'Kami rindu! Diskon spesial untuk sewa berikutnya.',
                    'percentage',
                    10,
                    2,
                    100000,
                ));
                $count++;
            }
        });

        return $count;
    }

    private function birthdayVouchers(): int
    {
        $count = 0;

        Customer::active()
            ->whereMonth('date_of_birth', now()->month)
            ->whereDay('date_of_birth', now()->day)
            ->chunkById(500, function ($customers) use (&$count) {
                foreach ($customers as $customer) {
                    $code = "BDAY-{$customer->id}-".now()->format('Ym');

                    if ($this->voucherExists($code)) {
                        continue;
                    }

                    PromoVoucher::create($this->voucherData(
                        $code,
                        'Birthday '.$customer->name,
                        'Selamat ulang tahun! Diskon spesial dari kami.',
                        'fixed',
                        150000,
                        1,
                        150000,
                    ));
                    $count++;
                }
            });

        return $count;
    }

    private function voucherExists(string $code): bool
    {
        return PromoVoucher::where('code', $code)->exists();
    }

    private function voucherData(string $code, string $name, string $description, string $type, float $value, int $minDays, float $maxDiscount): array
    {
        return [
            'code' => $code,
            'name' => $name,
            'description' => $description,
            'discount_type' => $type,
            'discount_value' => $value,
            'min_rental_days' => $minDays,
            'max_discount' => $maxDiscount,
            'usage_limit' => 1,
            'used_count' => 0,
            'start_date' => today(),
            'end_date' => now()->addDays(30),
            'is_active' => true,
        ];
    }
}

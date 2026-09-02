<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\PromoVoucher;
use App\Models\Testimonial;
use Illuminate\Database\Seeder;

/**
 * Demo storefront content: active promo vouchers and customer testimonials.
 * Idempotent — matched on code / customer email.
 */
class StorefrontContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedPromos();
        $this->seedTestimonials();
    }

    private function seedPromos(): void
    {
        $vouchers = [
            [
                'code' => 'DEMO-HEMAT10',
                'name' => 'Diskon 10% Sewa Mingguan',
                'description' => 'Diskon 10% untuk penyewaan minimal 3 hari, berlaku semua kategori.',
                'discount_type' => 'percentage',
                'discount_value' => 10,
                'max_discount' => 150000,
                'min_rental_days' => 3,
                'usage_limit' => 100,
                'start_date' => now()->subDays(3)->toDateString(),
                'end_date' => now()->addMonths(3)->toDateString(),
            ],
            [
                'code' => 'DEMO-WEEKEND50',
                'name' => 'Potongan Rp50.000 Akhir Pekan',
                'description' => 'Potongan langsung Rp50.000 tanpa minimum durasi.',
                'discount_type' => 'fixed',
                'discount_value' => 50000,
                'usage_limit' => 200,
                'start_date' => now()->subDay()->toDateString(),
                'end_date' => now()->addMonths(2)->toDateString(),
            ],
        ];

        foreach ($vouchers as $voucher) {
            PromoVoucher::updateOrCreate(['code' => $voucher['code']], $voucher + ['is_active' => true]);
        }
    }

    private function seedTestimonials(): void
    {
        $reviews = [
            [
                'email' => 'siti@gmail.com',
                'name' => 'Siti Rahayu',
                'company' => 'Perjalanan keluarga',
                'rating' => 5,
                'content' => 'Proses booking cepat dan jelas. Mobil datang bersih, dokumen digital membuat serah terima hanya butuh beberapa menit.',
            ],
            [
                'email' => 'ahmad@gmail.com',
                'name' => 'Ahmad Fauzi',
                'company' => 'Perjalanan bisnis',
                'rating' => 5,
                'content' => 'Harga transparan tanpa biaya kejutan. Rincian tarif, deposit, dan pajak langsung terlihat sebelum konfirmasi.',
            ],
            [
                'email' => 'dewi@gmail.com',
                'name' => 'Dewi Lestari',
                'company' => 'Wisata akhir pekan',
                'rating' => 4,
                'content' => 'Kondisi mobil terawat dan tim responsif saat saya bertanya soal rute. Pasti sewa lagi untuk perjalanan berikutnya.',
            ],
            [
                'email' => 'rudi@gmail.com',
                'name' => 'Rudi Hartono',
                'company' => 'Rental bulanan',
                'rating' => 5,
                'content' => 'Saya ambil paket bulanan untuk operasional kantor. Penjadwalan servis dan laporan pemakaian membantu sekali.',
            ],
        ];

        foreach ($reviews as $review) {
            $customer = Customer::where('email', $review['email'])->first();

            Testimonial::updateOrCreate(
                ['name' => $review['name'], 'content' => $review['content']],
                [
                    'customer_id' => $customer?->id,
                    'company' => $review['company'],
                    'rating' => $review['rating'],
                    'is_active' => true,
                ]
            );
        }
    }
}

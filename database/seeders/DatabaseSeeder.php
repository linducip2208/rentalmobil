<?php

namespace Database\Seeders;

use App\Models\Addon;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\Brand;
use App\Models\Category;
use App\Models\ChartOfAccount;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\Faq;
use App\Models\Location;
use App\Models\PaymentMethod;
use App\Models\SystemSetting;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedUsers();
        $this->seedCategories();
        $this->seedBrands();
        $this->seedLocations();
        $this->seedVehicles();
        $this->seedCustomers();
        $this->seedDrivers();
        $this->seedPaymentMethods();
        $this->seedAddons();
        $this->seedBlogCategories();
        $this->seedBlogPosts();
        $this->seedFaqs();
        $this->seedSystemSettings();
        $this->seedChartOfAccounts();
    }

    protected function seedUsers(): void
    {
        $users = [
            ['name' => 'Owner RentalMobil', 'email' => 'admin@rentalmobil.test', 'phone' => '081234567890', 'role' => 'owner'],
            ['name' => 'Manager Demo', 'email' => 'manager@rentalmobil.test', 'phone' => '081234567801', 'role' => 'manager'],
            ['name' => 'Admin Demo', 'email' => 'admin2@rentalmobil.test', 'phone' => '081234567802', 'role' => 'admin'],
            ['name' => 'Kasir Demo', 'email' => 'kasir@rentalmobil.test', 'phone' => '081234567803', 'role' => 'cashier'],
            ['name' => 'Driver Demo', 'email' => 'driver@rentalmobil.test', 'phone' => '081234567804', 'role' => 'driver'],
            ['name' => 'Finance Demo', 'email' => 'finance@rentalmobil.test', 'phone' => '081234567805', 'role' => 'finance'],
            ['name' => 'Mekanik Demo', 'email' => 'mekanik@rentalmobil.test', 'phone' => '081234567806', 'role' => 'mechanic'],
        ];
        foreach ($users as $user) {
            User::updateOrCreate(['email' => $user['email']], array_merge($user, [
                'password' => 'password', 'is_active' => true, 'email_verified_at' => now(),
            ]));
        }
    }

    protected function seedCategories(): void
    {
        $categories = [
            ['name' => 'SUV', 'sort_order' => 1],
            ['name' => 'Sedan', 'sort_order' => 2],
            ['name' => 'MPV', 'sort_order' => 3],
            ['name' => 'Pickup', 'sort_order' => 4],
            ['name' => 'Electric', 'sort_order' => 5],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['name' => $category['name']],
                array_merge($category, ['is_active' => true, 'slug' => \Illuminate\Support\Str::slug($category['name'])])
            );
        }
    }

    protected function seedBrands(): void
    {
        $brands = [
            ['name' => 'Toyota', 'country' => 'Jepang'],
            ['name' => 'Honda', 'country' => 'Jepang'],
            ['name' => 'Daihatsu', 'country' => 'Jepang'],
            ['name' => 'Mitsubishi', 'country' => 'Jepang'],
            ['name' => 'Hyundai', 'country' => 'Korea Selatan'],
        ];

        foreach ($brands as $brand) {
            Brand::updateOrCreate(
                ['name' => $brand['name']],
                array_merge($brand, ['is_active' => true, 'slug' => \Illuminate\Support\Str::slug($brand['name'])])
            );
        }
    }

    protected function seedLocations(): void
    {
        $locations = [
            [
                'name' => 'Jakarta Pusat',
                'address' => 'Jl. Sudirman No. 123, Jakarta Pusat',
                'city' => 'Jakarta',
                'province' => 'DKI Jakarta',
                'latitude' => -6.2088,
                'longitude' => 106.8456,
                'phone' => '021-555-0101',
                'is_headquarters' => true,
            ],
            [
                'name' => 'Bandung',
                'address' => 'Jl. Asia Afrika No. 45, Bandung',
                'city' => 'Bandung',
                'province' => 'Jawa Barat',
                'latitude' => -6.9175,
                'longitude' => 107.6191,
                'phone' => '022-555-0202',
            ],
            [
                'name' => 'Surabaya',
                'address' => 'Jl. Pemuda No. 67, Surabaya',
                'city' => 'Surabaya',
                'province' => 'Jawa Timur',
                'latitude' => -7.2575,
                'longitude' => 112.7521,
                'phone' => '031-555-0303',
            ],
        ];

        foreach ($locations as $location) {
            Location::updateOrCreate(
                ['name' => $location['name']],
                array_merge($location, ['is_active' => true, 'slug' => \Illuminate\Support\Str::slug($location['name'])])
            );
        }
    }

    protected function seedVehicles(): void
    {
        $vehicles = [
            [
                'name' => 'Toyota Avanza',
                'category' => 'MPV',
                'brand' => 'Toyota',
                'location' => 'Jakarta Pusat',
                'plate_number' => 'B 1234 ABC',
                'year' => 2023,
                'color' => 'Putih',
                'transmission' => 'automatic',
                'fuel_type' => 'pertalite',
                'seat_count' => 7,
                'mileage' => 15000,
                'daily_rate' => 350000,
                'weekly_rate' => 2100000,
                'monthly_rate' => 7500000,
                'late_fee_per_hour' => 25000,
                'deposit_amount' => 500000,
                'features' => ['AC', 'Audio System', 'USB Charger'],
            ],
            [
                'name' => 'Toyota Innova Reborn',
                'category' => 'MPV',
                'brand' => 'Toyota',
                'location' => 'Jakarta Pusat',
                'plate_number' => 'B 2345 DEF',
                'year' => 2024,
                'color' => 'Hitam',
                'transmission' => 'automatic',
                'fuel_type' => 'diesel',
                'seat_count' => 7,
                'mileage' => 8000,
                'daily_rate' => 550000,
                'weekly_rate' => 3300000,
                'monthly_rate' => 12000000,
                'late_fee_per_hour' => 40000,
                'deposit_amount' => 1000000,
                'features' => ['AC', 'Audio System', 'USB Charger', 'Captain Seat'],
            ],
            [
                'name' => 'Honda Brio',
                'category' => 'Sedan',
                'brand' => 'Honda',
                'location' => 'Bandung',
                'plate_number' => 'D 3456 GHI',
                'year' => 2023,
                'color' => 'Merah',
                'transmission' => 'automatic',
                'fuel_type' => 'pertalite',
                'seat_count' => 5,
                'mileage' => 20000,
                'daily_rate' => 275000,
                'weekly_rate' => 1650000,
                'monthly_rate' => 6000000,
                'late_fee_per_hour' => 20000,
                'deposit_amount' => 400000,
                'features' => ['AC', 'Audio System', 'Rear Camera'],
            ],
            [
                'name' => 'Honda HR-V',
                'category' => 'SUV',
                'brand' => 'Honda',
                'location' => 'Jakarta Pusat',
                'plate_number' => 'B 4567 JKL',
                'year' => 2024,
                'color' => 'Silver',
                'transmission' => 'automatic',
                'fuel_type' => 'pertamax',
                'seat_count' => 5,
                'mileage' => 5000,
                'daily_rate' => 450000,
                'weekly_rate' => 2700000,
                'monthly_rate' => 9500000,
                'late_fee_per_hour' => 30000,
                'deposit_amount' => 800000,
                'features' => ['AC', 'Audio System', 'Honda Sensing', 'Sunroof'],
            ],
            [
                'name' => 'Daihatsu Xenia',
                'category' => 'MPV',
                'brand' => 'Daihatsu',
                'location' => 'Surabaya',
                'plate_number' => 'L 5678 MNO',
                'year' => 2023,
                'color' => 'Biru',
                'transmission' => 'manual',
                'fuel_type' => 'pertalite',
                'seat_count' => 7,
                'mileage' => 25000,
                'daily_rate' => 300000,
                'weekly_rate' => 1800000,
                'monthly_rate' => 6500000,
                'late_fee_per_hour' => 20000,
                'deposit_amount' => 400000,
                'features' => ['AC', 'Audio System', 'USB Charger'],
            ],
            [
                'name' => 'Mitsubishi Pajero Sport',
                'category' => 'SUV',
                'brand' => 'Mitsubishi',
                'location' => 'Jakarta Pusat',
                'plate_number' => 'B 6789 PQR',
                'year' => 2024,
                'color' => 'Putih',
                'transmission' => 'automatic',
                'fuel_type' => 'diesel',
                'seat_count' => 7,
                'mileage' => 3000,
                'daily_rate' => 750000,
                'weekly_rate' => 4500000,
                'monthly_rate' => 16000000,
                'late_fee_per_hour' => 50000,
                'deposit_amount' => 1500000,
                'features' => ['AC', 'Audio System', '4WD', 'Cruise Control'],
            ],
            [
                'name' => 'Hyundai Creta',
                'category' => 'SUV',
                'brand' => 'Hyundai',
                'location' => 'Bandung',
                'plate_number' => 'D 7890 STU',
                'year' => 2024,
                'color' => 'Abu-abu',
                'transmission' => 'automatic',
                'fuel_type' => 'pertamax',
                'seat_count' => 5,
                'mileage' => 7000,
                'daily_rate' => 400000,
                'weekly_rate' => 2400000,
                'monthly_rate' => 8500000,
                'late_fee_per_hour' => 30000,
                'deposit_amount' => 700000,
                'features' => ['AC', 'Audio System', 'Blind Spot Monitor', 'Wireless Charger'],
            ],
            [
                'name' => 'Toyota Hilux',
                'category' => 'Pickup',
                'brand' => 'Toyota',
                'location' => 'Surabaya',
                'plate_number' => 'L 8901 VWX',
                'year' => 2023,
                'color' => 'Silver',
                'transmission' => 'manual',
                'fuel_type' => 'diesel',
                'seat_count' => 2,
                'mileage' => 30000,
                'daily_rate' => 450000,
                'weekly_rate' => 2700000,
                'monthly_rate' => 9000000,
                'late_fee_per_hour' => 35000,
                'deposit_amount' => 800000,
                'features' => ['AC', 'Audio System', '4WD', 'Cargo Bed'],
            ],
            [
                'name' => 'Mitsubishi L300',
                'category' => 'Pickup',
                'brand' => 'Mitsubishi',
                'location' => 'Bandung',
                'plate_number' => 'D 9012 YZA',
                'year' => 2022,
                'color' => 'Putih',
                'transmission' => 'manual',
                'fuel_type' => 'diesel',
                'seat_count' => 2,
                'mileage' => 45000,
                'daily_rate' => 400000,
                'weekly_rate' => 2400000,
                'monthly_rate' => 8500000,
                'late_fee_per_hour' => 30000,
                'deposit_amount' => 700000,
                'features' => ['AC', 'Audio System', 'Cargo Bed'],
            ],
            [
                'name' => 'Hyundai Ioniq 5',
                'category' => 'Electric',
                'brand' => 'Hyundai',
                'location' => 'Jakarta Pusat',
                'plate_number' => 'B 0123 BCD',
                'year' => 2024,
                'color' => 'Putih',
                'transmission' => 'automatic',
                'fuel_type' => 'electric',
                'seat_count' => 5,
                'mileage' => 2000,
                'daily_rate' => 800000,
                'weekly_rate' => 4800000,
                'monthly_rate' => 18000000,
                'late_fee_per_hour' => 60000,
                'deposit_amount' => 2000000,
                'features' => ['AC', 'Audio System', 'Fast Charging', 'ADAS', 'Vehicle to Load'],
            ],
        ];

        foreach ($vehicles as $vehicle) {
            $category = Category::where('name', $vehicle['category'])->first();
            $brand = Brand::where('name', $vehicle['brand'])->first();
            $location = Location::where('name', $vehicle['location'])->first();

            Vehicle::updateOrCreate(
                ['plate_number' => $vehicle['plate_number']],
                [
                    'name' => $vehicle['name'],
                    'slug' => \Illuminate\Support\Str::slug($vehicle['name']),
                    'category_id' => $category?->id,
                    'brand_id' => $brand?->id,
                    'location_id' => $location?->id,
                    'year' => $vehicle['year'],
                    'color' => $vehicle['color'],
                    'transmission' => $vehicle['transmission'],
                    'fuel_type' => $vehicle['fuel_type'],
                    'seat_count' => $vehicle['seat_count'],
                    'mileage' => $vehicle['mileage'],
                    'daily_rate' => $vehicle['daily_rate'],
                    'weekly_rate' => $vehicle['weekly_rate'],
                    'monthly_rate' => $vehicle['monthly_rate'],
                    'late_fee_per_hour' => $vehicle['late_fee_per_hour'],
                    'late_fee_per_day' => $vehicle['late_fee_per_hour'] * 8,
                    'deposit_amount' => $vehicle['deposit_amount'],
                    'status' => 'available',
                    'is_active' => true,
                    'features' => $vehicle['features'],
                ]
            );
        }
    }

    protected function seedCustomers(): void
    {
        $customers = [
            ['name' => 'Budi Santoso', 'email' => 'customer@rentalmobil.test', 'phone' => '081234567891', 'address' => 'Jl. Gatot Subroto No. 10, Jakarta', 'city' => 'Jakarta', 'province' => 'DKI Jakarta', 'ktp_number' => '3171234567890001'],
            ['name' => 'Siti Rahayu', 'email' => 'siti@gmail.com', 'phone' => '081234567892', 'address' => 'Jl. Merdeka No. 25, Bandung', 'city' => 'Bandung', 'province' => 'Jawa Barat', 'ktp_number' => '3273456789010002'],
            ['name' => 'Ahmad Fauzi', 'email' => 'ahmad@gmail.com', 'phone' => '081234567893', 'address' => 'Jl. Pahlawan No. 30, Surabaya', 'city' => 'Surabaya', 'province' => 'Jawa Timur', 'ktp_number' => '3578901234560003'],
            ['name' => 'Dewi Lestari', 'email' => 'dewi@gmail.com', 'phone' => '081234567894', 'address' => 'Jl. Asia Afrika No. 15, Bandung', 'city' => 'Bandung', 'province' => 'Jawa Barat', 'ktp_number' => '3273123456780004'],
            ['name' => 'Rudi Hartono', 'email' => 'rudi@gmail.com', 'phone' => '081234567895', 'address' => 'Jl. Sudirman No. 50, Jakarta', 'city' => 'Jakarta', 'province' => 'DKI Jakarta', 'ktp_number' => '3171567890120005'],
        ];

        foreach ($customers as $customer) {
            Customer::updateOrCreate(
                ['phone' => $customer['phone']],
                array_merge($customer, [
                    'customer_type' => 'individual',
                    'trust_score' => 80,
                    'total_spent' => 0,
                    'total_orders' => 0,
                    'loyalty_tier' => 'bronze',
                    'verification_status' => 'verified',
                    'password' => 'password',
                    'is_active' => true,
                ])
            );
        }
    }

    protected function seedDrivers(): void
    {
        $jakarta = Location::where('name', 'Jakarta Pusat')->first();
        $bandung = Location::where('name', 'Bandung')->first();
        $surabaya = Location::where('name', 'Surabaya')->first();

        $drivers = [
            ['name' => 'Andi Pratama', 'sim_number' => 'SIM-A-12345', 'phone' => '081987654321', 'address' => 'Jl. Diponegoro No. 5, Jakarta', 'sim_type' => 'A', 'sim_expiry' => '2030-12-31', 'location_id' => $jakarta?->id],
            ['name' => 'Joko Widodo', 'sim_number' => 'SIM-A-23456', 'phone' => '081987654322', 'address' => 'Jl. Ahmad Yani No. 12, Bandung', 'sim_type' => 'A', 'sim_expiry' => '2030-12-31', 'location_id' => $bandung?->id],
            ['name' => 'Hendra Kusuma', 'sim_number' => 'SIM-A-34567', 'phone' => '081987654323', 'address' => 'Jl. Thamrin No. 8, Surabaya', 'sim_type' => 'A', 'sim_expiry' => '2030-12-31', 'location_id' => $surabaya?->id],
        ];

        foreach ($drivers as $driver) {
            Driver::updateOrCreate(
                ['sim_number' => $driver['sim_number']],
                array_merge($driver, [
                    'is_active' => true,
                    'is_available' => true,
                    'rating' => 5.00,
                    'total_trips' => 0,
                ])
            );
        }
    }

    protected function seedPaymentMethods(): void
    {
        $methods = [
            ['name' => 'Transfer Bank BCA', 'code' => 'BCA', 'type' => 'bank_transfer', 'icon' => 'fas fa-university', 'sort_order' => 1],
            ['name' => 'Transfer Bank Mandiri', 'code' => 'MANDIRI', 'type' => 'bank_transfer', 'icon' => 'fas fa-university', 'sort_order' => 2],
            ['name' => 'Cash', 'code' => 'CASH', 'type' => 'cash', 'icon' => 'fas fa-money-bill-wave', 'sort_order' => 3],
            ['name' => 'QRIS', 'code' => 'QRIS', 'type' => 'qris', 'icon' => 'fas fa-qrcode', 'sort_order' => 4],
        ];

        foreach ($methods as $method) {
            PaymentMethod::updateOrCreate(
                ['code' => $method['code']],
                array_merge($method, ['is_active' => true])
            );
        }
    }

    protected function seedAddons(): void
    {
        $addons = [
            ['name' => 'Asuransi Kendaraan', 'slug' => 'asuransi-kendaraan', 'description' => 'Perlindungan asuransi all-risk untuk kendaraan', 'price_type' => 'daily', 'price' => 50000],
            ['name' => 'Supir', 'slug' => 'supir', 'description' => 'Layanan supir berpengalaman', 'price_type' => 'daily', 'price' => 150000, 'requires_driver' => true],
            ['name' => 'Baby Seat', 'slug' => 'baby-seat', 'description' => 'Kursi bayi untuk perjalanan aman', 'price_type' => 'fixed', 'price' => 25000],
            ['name' => 'GPS Navigator', 'slug' => 'gps-navigator', 'description' => 'Perangkat GPS untuk navigasi', 'price_type' => 'daily', 'price' => 15000],
        ];

        foreach ($addons as $addon) {
            Addon::updateOrCreate(
                ['slug' => $addon['slug']],
                array_merge($addon, ['is_active' => true])
            );
        }
    }

    protected function seedBlogCategories(): void
    {
        $categories = [
            ['name' => 'Tips Rental', 'slug' => 'tips-rental', 'description' => 'Tips dan trik seputar rental kendaraan'],
            ['name' => 'Wisata', 'slug' => 'wisata', 'description' => 'Rekomendasi wisata dan perjalanan'],
            ['name' => 'Otomotif', 'slug' => 'otomotif', 'description' => 'Berita dan tips otomotif'],
        ];

        foreach ($categories as $category) {
            BlogCategory::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }

    protected function seedBlogPosts(): void
    {
        $author = User::where('email', 'admin@rentalmobil.test')->first();
        $tipsCategory = BlogCategory::where('slug', 'tips-rental')->first();
        $wisataCategory = BlogCategory::where('slug', 'wisata')->first();
        $otomotifCategory = BlogCategory::where('slug', 'otomotif')->first();

        $posts = [
            [
                'title' => 'Tips Memilih Kendaraan Rental yang Tepat',
                'slug' => 'tips-memilih-kendaraan-rental',
                'excerpt' => 'Panduan lengkap memilih kendaraan rental sesuai kebutuhan perjalanan Anda.',
                'content' => '<p>Memilih kendaraan rental yang tepat sangat penting untuk kenyamanan perjalanan. Berikut beberapa tips yang bisa membantu Anda:</p><h3>1. Tentukan Kebutuhan</h3><p>Apakah Anda bepergian sendiri, berdua, atau bersama keluarga besar? Jumlah penumpang akan menentukan jenis kendaraan yang cocok.</p><h3>2. Perhatikan Jenis Medan</h3><p>Jika akan melewati medan pegunungan, SUV atau MPV dengan mesin bertenaga bisa menjadi pilihan terbaik.</p><h3>3. Sesuaikan dengan Budget</h3><p>Tentukan budget harian untuk rental dan pilih kendaraan yang sesuai. Jangan lupa untuk memperhitungkan biaya bahan bakar.</p>',
                'category_id' => $tipsCategory?->id,
                'author_id' => $author?->id,
                'is_published' => true,
                'published_at' => now()->subDays(5),
                'meta_title' => 'Tips Memilih Kendaraan Rental - RentalMobil',
                'meta_description' => 'Panduan lengkap memilih kendaraan rental sesuai kebutuhan perjalanan Anda.',
            ],
            [
                'title' => 'Wisata Jalur Pantura: Jakarta ke Semarang',
                'slug' => 'wisata-jalur-pantura-jakarta-semarang',
                'excerpt' => 'Jelajahi keindahan jalur pantai utara Jawa dari Jakarta ke Semarang.',
                'content' => '<p>Jalur Pantura (Pantai Utara Jawa) menawarkan perjalanan yang menarik dari Jakarta ke Semarang. Berikut rekomendasi perjalanan:</p><h3>Hari 1: Jakarta - Cirebon</h3><p>Perjalanan sekitar 3-4 jam. Singgah di Keraton Kasepuhan dan nikmati kuliner empal gentong.</p><h3>Hari 2: Cirebon - Semarang</h3><p>Lanjutkan perjalanan ke Semarang. Kunjungi Lawang Sewu dan kota lama Semarang.</p>',
                'category_id' => $wisataCategory?->id,
                'author_id' => $author?->id,
                'is_published' => true,
                'published_at' => now()->subDays(3),
                'meta_title' => 'Wisata Jalur Pantura - RentalMobil',
                'meta_description' => 'Jelajahi keindahan jalur pantai utara Jawa dari Jakarta ke Semarang.',
            ],
            [
                'title' => 'Perawatan Mobil Sebelum Perjalanan Jauh',
                'slug' => 'perawatan-mobil-perjalanan-jauh',
                'excerpt' => 'Checklist perawatan mobil wajib sebelum memulai perjalanan jauh.',
                'content' => '<p>Sebelum memulai perjalanan jauh, pastikan kendaraan dalam kondisi prima. Berikut checklist yang perlu diperhatikan:</p><h3>1. Cek Oli Mesin</h3><p>Pastikan level oli mesin masih dalam batas normal. Ganti oli jika sudah melewati jarak tempuh yang direkomendasikan.</p><h3>2. Cek Tekanan Ban</h3><p>Tekanan ban yang tepat sangat penting untuk keselamatan dan efisiensi bahan bakar.</p><h3>3. Cek Sistem Rem</h3><p>Pastikan sistem rem berfungsi dengan baik. Periksa ketebalan kampas rem dan cairan rem.</p>',
                'category_id' => $otomotifCategory?->id,
                'author_id' => $author?->id,
                'is_published' => true,
                'published_at' => now()->subDay(),
                'meta_title' => 'Perawatan Mobil Sebelum Perjalanan Jauh - RentalMobil',
                'meta_description' => 'Checklist perawatan mobil wajib sebelum memulai perjalanan jauh.',
            ],
            [
                'title' => 'Rental Mobil untuk Liburan Keluarga',
                'slug' => 'rental-mobil-liburan-keluarga',
                'excerpt' => 'Rekomendasi kendaraan rental terbaik untuk liburan keluarga.',
                'content' => '<p>Liburan keluarga akan lebih menyenangkan dengan kendaraan yang nyaman. Berikut rekomendasi kami:</p><h3>MPV: Toyota Innova</h3><p>Kapasitas 7 penumpang, bagasi luas, dan suspensi nyaman untuk perjalanan jauh.</p><h3>SUV: Honda HR-V</h3><p>Cocok untuk medan yang bervariasi dengan fitur keselamatan lengkap.</p>',
                'category_id' => $tipsCategory?->id,
                'author_id' => $author?->id,
                'is_published' => true,
                'published_at' => now()->subDays(7),
                'meta_title' => 'Rental Mobil Liburan Keluarga - RentalMobil',
                'meta_description' => 'Rekomendasi kendaraan rental terbaik untuk liburan keluarga.',
            ],
            [
                'title' => 'Keunggulan Mobil Listrik untuk Perjalanan Kota',
                'slug' => 'keunggulan-mobil-listrik',
                'excerpt' => 'Mengapa mobil listrik menjadi pilihan cerdas untuk perjalanan dalam kota.',
                'content' => '<p>Mobil listrik menawarkan banyak keunggulan untuk perjalanan dalam kota. Berikut alasannya:</p><h3>Hemat Biaya</h3><p>Biaya listrik jauh lebih murah dibandingkan bahan bakar fosil. Penghematan bisa mencapai 70%.</p><h3>Ramah Lingkungan</h3><p>Tidak ada emisi gas buang yang mencemari udara kota.</p><h3>Perawatan Minimal</h3><p>Mesin elektrik memiliki komponen yang lebih sedikit dibandingkan mesin konvensional.</p>',
                'category_id' => $otomotifCategory?->id,
                'author_id' => $author?->id,
                'is_published' => true,
                'published_at' => now()->subDays(10),
                'meta_title' => 'Keunggulan Mobil Listrik - RentalMobil',
                'meta_description' => 'Mengapa mobil listrik menjadi pilihan cerdas untuk perjalanan dalam kota.',
            ],
        ];

        foreach ($posts as $post) {
            BlogPost::updateOrCreate(
                ['slug' => $post['slug']],
                array_merge($post, [
                    'views' => rand(50, 500),
                ])
            );
        }
    }

    protected function seedFaqs(): void
    {
        $faqs = [
            ['question' => 'Bagaimana cara menyewa mobil?', 'answer' => 'Pilih kendaraan yang diinginkan, tentukan tanggal dan lokasi pengambilan, lalu lakukan pemesanan melalui website atau WhatsApp kami. Tim kami akan mengkonfirmasi pesanan Anda.', 'sort_order' => 1],
            ['question' => 'Dokumen apa saja yang diperlukan untuk menyewa mobil?', 'answer' => 'Anda perlu menyiapkan KTP asli, SIM A yang masih berlaku, dan kartu kredit (untuk deposit). Untuk pelanggan korporat, diperlukan juga surat keterangan perusahaan.', 'sort_order' => 2],
            ['question' => 'Apakah bisa menyewa mobil dengan supir?', 'answer' => 'Ya, kami menyediakan layanan supir berpengalaman. Biaya supir adalah Rp 150.000/hari. Supir kami sudah berpengalaman dan mengetahui rute perjalanan dengan baik.', 'sort_order' => 3],
            ['question' => 'Bagaimana jika mobil mengalami kerusakan saat disewa?', 'answer' => 'Segera hubungi tim customer service kami. Kami akan memberikan bantuan darurat dan penggantian kendaraan jika diperlukan. Asuransi all-risk tersedia untuk perlindungan optimal.', 'sort_order' => 4],
            ['question' => 'Bisakah saya mengembalikan mobil di lokasi yang berbeda?', 'answer' => 'Ya, kami menyediakan layanan one-way rental dengan biaya tambahan. Silakan hubungi kami untuk informasi lebih lanjut mengenai biaya dan ketersediaan.', 'sort_order' => 5],
            ['question' => 'Apa yang terjadi jika saya terlambat mengembalikan mobil?', 'answer' => 'Biaya keterlambatan dikenakan per jam sesuai ketentuan. Jika terlambat lebih dari 24 jam, status pesanan akan diubah menjadi overdue dan akan ada biaya tambahan.', 'sort_order' => 6],
            ['question' => 'Bagaimana cara pembayaran?', 'answer' => 'Kami menerima pembayaran melalui transfer bank (BCA, Mandiri, BRI), tunai saat pengambilan kendaraan, dan QRIS. Pembayaran deposit diperlukan saat pemesanan.', 'sort_order' => 7],
            ['question' => 'Apakah harga sudah termasuk BBM?', 'answer' => 'Harga rental belum termasuk BBM. Kendaraan dikirim dengan kondisi BBM penuh dan harus dikembalikan dalam kondisi yang sama.', 'sort_order' => 8],
            ['question' => 'Bagaimana jika saya ingin membatalkan pesanan?', 'answer' => 'Pembatalan yang dilakukan lebih dari 48 jam sebelum jadwal pengambilan akan mendapat pengembalian deposit penuh. Pembatalan kurang dari 48 jam dikenakan biaya pembatalan 50%.', 'sort_order' => 9],
            ['question' => 'Apakah ada batasan kilometer?', 'answer' => 'Tidak ada batasan kilometer untuk rental harian dan mingguan. Namun, untuk rental bulanan, batas normal adalah 4.000 km/bulan. Kelebihan akan dikenakan biaya Rp 2.000/km.', 'sort_order' => 10],
        ];

        foreach ($faqs as $faq) {
            Faq::updateOrCreate(
                ['question' => $faq['question']],
                $faq
            );
        }
    }

    protected function seedSystemSettings(): void
    {
        $settings = [
            ['key' => 'app_name', 'value' => 'RentalMobil', 'type' => 'string', 'group_name' => 'general', 'description' => 'Nama aplikasi'],
            ['key' => 'company_name', 'value' => 'PT RentalMobil Indonesia', 'type' => 'string', 'group_name' => 'general', 'description' => 'Nama perusahaan'],
            ['key' => 'company_address', 'value' => 'Jl. Sudirman No. 123, Jakarta Pusat', 'type' => 'string', 'group_name' => 'general', 'description' => 'Alamat perusahaan'],
            ['key' => 'company_phone', 'value' => '021-555-0101', 'type' => 'string', 'group_name' => 'general', 'description' => 'Telepon perusahaan'],
            ['key' => 'company_email', 'value' => 'info@rentalmobil.test', 'type' => 'string', 'group_name' => 'general', 'description' => 'Email perusahaan'],
            ['key' => 'currency', 'value' => 'IDR', 'type' => 'string', 'group_name' => 'finance', 'description' => 'Mata uang'],
            ['key' => 'currency_symbol', 'value' => 'Rp', 'type' => 'string', 'group_name' => 'finance', 'description' => 'Simbol mata uang'],
            ['key' => 'tax_rate', 'value' => '0.11', 'type' => 'string', 'group_name' => 'finance', 'description' => 'Tarif PPN 11%'],
            ['key' => 'overdue_threshold_hours', 'value' => '24', 'type' => 'string', 'group_name' => 'rental', 'description' => 'Jam sebelum overdue'],
            ['key' => 'missing_threshold_hours', 'value' => '72', 'type' => 'string', 'group_name' => 'rental', 'description' => 'Jam sebelum missing'],
            ['key' => 'driver_daily_cost', 'value' => '150000', 'type' => 'string', 'group_name' => 'rental', 'description' => 'Biaya supir/hari'],
        ];

        foreach ($settings as $setting) {
            SystemSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }

    protected function seedChartOfAccounts(): void
    {
        $accounts = [
            ['code' => '1100', 'name' => 'Kas', 'type' => 'asset', 'normal_balance' => 'debit'],
            ['code' => '1110', 'name' => 'Bank BCA', 'type' => 'asset', 'normal_balance' => 'debit'],
            ['code' => '1120', 'name' => 'Bank Mandiri', 'type' => 'asset', 'normal_balance' => 'debit'],
            ['code' => '1200', 'name' => 'Piutang Usaha', 'type' => 'asset', 'normal_balance' => 'debit'],
            ['code' => '1500', 'name' => 'Kendaraan', 'type' => 'asset', 'normal_balance' => 'debit'],
            ['code' => '2100', 'name' => 'Hutang Usaha', 'type' => 'liability', 'normal_balance' => 'credit'],
            ['code' => '2200', 'name' => 'Hutang Pajak', 'type' => 'liability', 'normal_balance' => 'credit'],
            ['code' => '3100', 'name' => 'Modal Disetor', 'type' => 'equity', 'normal_balance' => 'credit'],
            ['code' => '4100', 'name' => 'Pendapatan Sewa', 'type' => 'revenue', 'normal_balance' => 'credit'],
            ['code' => '4200', 'name' => 'Pendapatan Supir', 'type' => 'revenue', 'normal_balance' => 'credit'],
            ['code' => '4300', 'name' => 'Pendapatan Addon', 'type' => 'revenue', 'normal_balance' => 'credit'],
            ['code' => '5100', 'name' => 'Biaya BBM', 'type' => 'expense', 'normal_balance' => 'debit'],
            ['code' => '5200', 'name' => 'Biaya Perawatan', 'type' => 'expense', 'normal_balance' => 'debit'],
            ['code' => '5300', 'name' => 'Biaya Gaji Supir', 'type' => 'expense', 'normal_balance' => 'debit'],
            ['code' => '5400', 'name' => 'Biaya Asuransi', 'type' => 'expense', 'normal_balance' => 'debit'],
        ];

        foreach ($accounts as $account) {
            ChartOfAccount::updateOrCreate(
                ['code' => $account['code']],
                array_merge($account, ['is_active' => true])
            );
        }
    }
}

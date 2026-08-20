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
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

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
        User::updateOrCreate(
            ['email' => 'admin@rentalmobil.test'],
            [
                'name' => 'Admin RentalMobil',
                'password' => Hash::make('password'),
                'phone' => '081234567890',
                'role' => 'admin',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
    }

    protected function seedCategories(): void
    {
        $categories = [
            ['name' => 'SUV', 'description' => 'Sport Utility Vehicle - Cocok untuk perjalanan keluarga dan medan berat', 'sort_order' => 1],
            ['name' => 'Sedan', 'description' => 'Mobil sedan nyaman untuk perjalanan dalam kota', 'sort_order' => 2],
            ['name' => 'MPV', 'description' => 'Multi-Purpose Vehicle - Kapasitas besar untuk rombongan', 'sort_order' => 3],
            ['name' => 'Pickup', 'description' => 'Mobil pickup untuk kebutuhan angkut barang', 'sort_order' => 4],
            ['name' => 'Electric', 'description' => 'Kendaraan listrik ramah lingkungan', 'sort_order' => 5],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['name' => $category['name']],
                array_merge($category, ['is_active' => true])
            );
        }
    }

    protected function seedBrands(): void
    {
        $brands = [
            ['name' => 'Toyota', 'description' => 'Toyota - Mobil Terpercaya di Indonesia'],
            ['name' => 'Honda', 'description' => 'Honda - The Power of Dreams'],
            ['name' => 'Daihatsu', 'description' => 'Daihatsu - We Make People Smile'],
            ['name' => 'Mitsubishi', 'description' => 'Mitsubishi Motors - Drive your Ambition'],
            ['name' => 'Hyundai', 'description' => 'Hyundai - New Thinking. New Possibilities.'],
        ];

        foreach ($brands as $brand) {
            Brand::updateOrCreate(
                ['name' => $brand['name']],
                array_merge($brand, ['is_active' => true])
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
                'postal_code' => '10220',
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
                'postal_code' => '40111',
                'latitude' => -6.9175,
                'longitude' => 107.6191,
                'phone' => '022-555-0202',
                'is_headquarters' => false,
            ],
            [
                'name' => 'Surabaya',
                'address' => 'Jl. Pemuda No. 67, Surabaya',
                'city' => 'Surabaya',
                'province' => 'Jawa Timur',
                'postal_code' => '60281',
                'latitude' => -7.2575,
                'longitude' => 112.7521,
                'phone' => '031-555-0303',
                'is_headquarters' => false,
            ],
        ];

        foreach ($locations as $location) {
            Location::updateOrCreate(
                ['name' => $location['name']],
                array_merge($location, ['is_active' => true])
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
                'license_plate' => 'B 1234 ABC',
                'year' => 2023,
                'color' => 'Putih',
                'transmission' => 'Automatic',
                'fuel_type' => 'Gasoline',
                'seats' => 7,
                'engine_cc' => 1500,
                'daily_rate' => 350000,
                'weekly_rate' => 2100000,
                'monthly_rate' => 7500000,
                'late_fee_per_hour' => 25000,
                'deposit_amount' => 500000,
                'current_km' => 15000,
                'features' => ['AC', 'Audio System', 'USB Charger'],
            ],
            [
                'name' => 'Toyota Innova Reborn',
                'category' => 'MPV',
                'brand' => 'Toyota',
                'location' => 'Jakarta Pusat',
                'license_plate' => 'B 2345 DEF',
                'year' => 2024,
                'color' => 'Hitam',
                'transmission' => 'Automatic',
                'fuel_type' => 'Diesel',
                'seats' => 7,
                'engine_cc' => 2400,
                'daily_rate' => 550000,
                'weekly_rate' => 3300000,
                'monthly_rate' => 12000000,
                'late_fee_per_hour' => 40000,
                'deposit_amount' => 1000000,
                'current_km' => 8000,
                'features' => ['AC', 'Audio System', 'USB Charger', 'Captain Seat'],
            ],
            [
                'name' => 'Honda Brio',
                'category' => 'Sedan',
                'brand' => 'Honda',
                'location' => 'Bandung',
                'license_plate' => 'D 3456 GHI',
                'year' => 2023,
                'color' => 'Merah',
                'transmission' => 'Automatic',
                'fuel_type' => 'Gasoline',
                'seats' => 5,
                'engine_cc' => 1200,
                'daily_rate' => 275000,
                'weekly_rate' => 1650000,
                'monthly_rate' => 6000000,
                'late_fee_per_hour' => 20000,
                'deposit_amount' => 400000,
                'current_km' => 20000,
                'features' => ['AC', 'Audio System', 'Rear Camera'],
            ],
            [
                'name' => 'Honda HR-V',
                'category' => 'SUV',
                'brand' => 'Honda',
                'location' => 'Jakarta Pusat',
                'license_plate' => 'B 4567 JKL',
                'year' => 2024,
                'color' => 'Silver',
                'transmission' => 'Automatic',
                'fuel_type' => 'Gasoline',
                'seats' => 5,
                'engine_cc' => 1500,
                'daily_rate' => 450000,
                'weekly_rate' => 2700000,
                'monthly_rate' => 9500000,
                'late_fee_per_hour' => 30000,
                'deposit_amount' => 800000,
                'current_km' => 5000,
                'features' => ['AC', 'Audio System', 'Honda Sensing', 'Sunroof'],
            ],
            [
                'name' => 'Daihatsu Xenia',
                'category' => 'MPV',
                'brand' => 'Daihatsu',
                'location' => 'Surabaya',
                'license_plate' => 'L 5678 MNO',
                'year' => 2023,
                'color' => 'Biru',
                'transmission' => 'Manual',
                'fuel_type' => 'Gasoline',
                'seats' => 7,
                'engine_cc' => 1300,
                'daily_rate' => 300000,
                'weekly_rate' => 1800000,
                'monthly_rate' => 6500000,
                'late_fee_per_hour' => 20000,
                'deposit_amount' => 400000,
                'current_km' => 25000,
                'features' => ['AC', 'Audio System', 'USB Charger'],
            ],
            [
                'name' => 'Mitsubishi Pajero Sport',
                'category' => 'SUV',
                'brand' => 'Mitsubishi',
                'location' => 'Jakarta Pusat',
                'license_plate' => 'B 6789 PQR',
                'year' => 2024,
                'color' => 'Putih',
                'transmission' => 'Automatic',
                'fuel_type' => 'Diesel',
                'seats' => 7,
                'engine_cc' => 2400,
                'daily_rate' => 750000,
                'weekly_rate' => 4500000,
                'monthly_rate' => 16000000,
                'late_fee_per_hour' => 50000,
                'deposit_amount' => 1500000,
                'current_km' => 3000,
                'features' => ['AC', 'Audio System', '4WD', 'Cruise Control'],
            ],
            [
                'name' => 'Hyundai Creta',
                'category' => 'SUV',
                'brand' => 'Hyundai',
                'location' => 'Bandung',
                'license_plate' => 'D 7890 STU',
                'year' => 2024,
                'color' => 'Abu-abu',
                'transmission' => 'Automatic',
                'fuel_type' => 'Gasoline',
                'seats' => 5,
                'engine_cc' => 1500,
                'daily_rate' => 400000,
                'weekly_rate' => 2400000,
                'monthly_rate' => 8500000,
                'late_fee_per_hour' => 30000,
                'deposit_amount' => 700000,
                'current_km' => 7000,
                'features' => ['AC', 'Audio System', 'Blind Spot Monitor', 'Wireless Charger'],
            ],
            [
                'name' => 'Toyota Hilux',
                'category' => 'Pickup',
                'brand' => 'Toyota',
                'location' => 'Surabaya',
                'license_plate' => 'L 8901 VWX',
                'year' => 2023,
                'color' => 'Silver',
                'transmission' => 'Manual',
                'fuel_type' => 'Diesel',
                'seats' => 2,
                'engine_cc' => 2400,
                'daily_rate' => 450000,
                'weekly_rate' => 2700000,
                'monthly_rate' => 9000000,
                'late_fee_per_hour' => 35000,
                'deposit_amount' => 800000,
                'current_km' => 30000,
                'features' => ['AC', 'Audio System', '4WD', 'Cargo Bed'],
            ],
            [
                'name' => 'Mitsubishi L300',
                'category' => 'Pickup',
                'brand' => 'Mitsubishi',
                'location' => 'Bandung',
                'license_plate' => 'D 9012 YZA',
                'year' => 2022,
                'color' => 'Putih',
                'transmission' => 'Manual',
                'fuel_type' => 'Diesel',
                'seats' => 2,
                'engine_cc' => 2500,
                'daily_rate' => 400000,
                'weekly_rate' => 2400000,
                'monthly_rate' => 8500000,
                'late_fee_per_hour' => 30000,
                'deposit_amount' => 700000,
                'current_km' => 45000,
                'features' => ['AC', 'Audio System', 'Cargo Bed'],
            ],
            [
                'name' => 'Hyundai Ioniq 5',
                'category' => 'Electric',
                'brand' => 'Hyundai',
                'location' => 'Jakarta Pusat',
                'license_plate' => 'B 0123 BCD',
                'year' => 2024,
                'color' => 'Putih',
                'transmission' => 'Automatic',
                'fuel_type' => 'Electric',
                'seats' => 5,
                'engine_cc' => 0,
                'daily_rate' => 800000,
                'weekly_rate' => 4800000,
                'monthly_rate' => 18000000,
                'late_fee_per_hour' => 60000,
                'deposit_amount' => 2000000,
                'current_km' => 2000,
                'features' => ['AC', 'Audio System', 'Fast Charging', 'ADAS', 'Vehicle to Load'],
            ],
        ];

        foreach ($vehicles as $vehicle) {
            $category = Category::where('name', $vehicle['category'])->first();
            $brand = Brand::where('name', $vehicle['brand'])->first();
            $location = Location::where('name', $vehicle['location'])->first();

            Vehicle::updateOrCreate(
                ['license_plate' => $vehicle['license_plate']],
                [
                    'name' => $vehicle['name'],
                    'category_id' => $category?->id,
                    'brand_id' => $brand?->id,
                    'location_id' => $location?->id,
                    'year' => $vehicle['year'],
                    'color' => $vehicle['color'],
                    'transmission' => $vehicle['transmission'],
                    'fuel_type' => $vehicle['fuel_type'],
                    'seats' => $vehicle['seats'],
                    'engine_cc' => $vehicle['engine_cc'],
                    'daily_rate' => $vehicle['daily_rate'],
                    'weekly_rate' => $vehicle['weekly_rate'],
                    'monthly_rate' => $vehicle['monthly_rate'],
                    'late_fee_per_hour' => $vehicle['late_fee_per_hour'],
                    'deposit_amount' => $vehicle['deposit_amount'],
                    'current_km' => $vehicle['current_km'],
                    'status' => 'available',
                    'is_active' => true,
                    'is_insured' => true,
                    'features' => $vehicle['features'],
                ]
            );
        }
    }

    protected function seedCustomers(): void
    {
        $customers = [
            ['name' => 'Budi Santoso', 'email' => 'budi@gmail.com', 'phone' => '081234567891', 'address' => 'Jl. Gatot Subroto No. 10, Jakarta', 'city' => 'Jakarta', 'province' => 'DKI Jakarta', 'id_card_type' => 'KTP', 'id_card_number' => '3171234567890001'],
            ['name' => 'Siti Rahayu', 'email' => 'siti@gmail.com', 'phone' => '081234567892', 'address' => 'Jl. Merdeka No. 25, Bandung', 'city' => 'Bandung', 'province' => 'Jawa Barat', 'id_card_type' => 'KTP', 'id_card_number' => '3273456789010002'],
            ['name' => 'Ahmad Fauzi', 'email' => 'ahmad@gmail.com', 'phone' => '081234567893', 'address' => 'Jl. Pahlawan No. 30, Surabaya', 'city' => 'Surabaya', 'province' => 'Jawa Timur', 'id_card_type' => 'KTP', 'id_card_number' => '3578901234560003'],
            ['name' => 'Dewi Lestari', 'email' => 'dewi@gmail.com', 'phone' => '081234567894', 'address' => 'Jl. Asia Afrika No. 15, Bandung', 'city' => 'Bandung', 'province' => 'Jawa Barat', 'id_card_type' => 'KTP', 'id_card_number' => '3273123456780004'],
            ['name' => 'Rudi Hartono', 'email' => 'rudi@gmail.com', 'phone' => '081234567895', 'address' => 'Jl. Sudirman No. 50, Jakarta', 'city' => 'Jakarta', 'province' => 'DKI Jakarta', 'id_card_type' => 'KTP', 'id_card_number' => '3171567890120005'],
        ];

        foreach ($customers as $customer) {
            Customer::updateOrCreate(
                ['email' => $customer['email']],
                array_merge($customer, [
                    'trust_score' => 80.00,
                    'total_spent' => 0,
                    'total_orders' => 0,
                    'is_active' => true,
                ])
            );
        }
    }

    protected function seedDrivers(): void
    {
        $drivers = [
            ['name' => 'Andi Pratama', 'license_number' => 'SIM-A-12345', 'phone' => '081987654321', 'address' => 'Jl. Diponegoro No. 5, Jakarta', 'emergency_contact' => '081111222333'],
            ['name' => 'Joko Widodo', 'license_number' => 'SIM-A-23456', 'phone' => '081987654322', 'address' => 'Jl. Ahmad Yani No. 12, Bandung', 'emergency_contact' => '081111222444'],
            ['name' => 'Hendra Kusuma', 'license_number' => 'SIM-A-34567', 'phone' => '081987654323', 'address' => 'Jl. Thamrin No. 8, Surabaya', 'emergency_contact' => '081111222555'],
        ];

        foreach ($drivers as $driver) {
            Driver::updateOrCreate(
                ['license_number' => $driver['license_number']],
                array_merge($driver, [
                    'license_expiry' => now()->addYears(5),
                    'status' => 'active',
                    'is_active' => true,
                    'rating' => 5.00,
                    'total_trips' => 0,
                ])
            );
        }
    }

    protected function seedPaymentMethods(): void
    {
        $methods = [
            ['name' => 'Transfer Bank', 'type' => 'bank_transfer', 'icon' => 'fas fa-university', 'bank_name' => 'Bank BCA', 'account_name' => 'PT RentalMobil Indonesia', 'account_number' => '1234567890', 'instructions' => 'Transfer ke rekening BCA di atas, konfirmasi via WhatsApp', 'sort_order' => 1],
            ['name' => 'Cash', 'type' => 'cash', 'icon' => 'fas fa-money-bill-wave', 'account_name' => '', 'account_number' => '', 'bank_name' => '', 'instructions' => 'Pembayaran tunai saat pengambilan kendaraan', 'sort_order' => 2],
            ['name' => 'QRIS', 'type' => 'qris', 'icon' => 'fas fa-qrcode', 'account_name' => 'PT RentalMobil Indonesia', 'account_number' => '', 'bank_name' => '', 'instructions' => 'Scan QR Code yang tersedia, konfirmasi via WhatsApp', 'sort_order' => 3],
        ];

        foreach ($methods as $method) {
            PaymentMethod::updateOrCreate(
                ['name' => $method['name']],
                array_merge($method, ['is_active' => true])
            );
        }
    }

    protected function seedAddons(): void
    {
        $addons = [
            ['name' => 'Asuransi Kendaraan', 'description' => 'Perlindungan asuransi all-risk untuk kendaraan', 'price' => 50000, 'type' => 'insurance', 'sort_order' => 1],
            ['name' => 'Supir', 'description' => 'Layanan supir berpengalaman', 'price' => 150000, 'type' => 'driver', 'sort_order' => 2],
            ['name' => 'Baby Seat', 'description' => 'Kursi bayi untuk perjalanan aman', 'price' => 25000, 'type' => 'accessory', 'sort_order' => 3],
            ['name' => 'GPS Navigator', 'description' => 'Perangkat GPS untuk navigasi', 'price' => 15000, 'type' => 'accessory', 'sort_order' => 4],
        ];

        foreach ($addons as $addon) {
            Addon::updateOrCreate(
                ['name' => $addon['name']],
                array_merge($addon, ['is_active' => true])
            );
        }
    }

    protected function seedBlogCategories(): void
    {
        $categories = [
            ['name' => 'Tips Rental', 'description' => 'Tips dan trik seputar rental kendaraan'],
            ['name' => 'Wisata', 'description' => 'Rekomendasi wisata dan perjalanan'],
            ['name' => 'Otomotif', 'description' => 'Berita dan tips otomotif'],
        ];

        foreach ($categories as $category) {
            BlogCategory::updateOrCreate(
                ['name' => $category['name']],
                array_merge($category, ['is_active' => true])
            );
        }
    }

    protected function seedBlogPosts(): void
    {
        $author = User::where('email', 'admin@rentalmobil.test')->first();
        $tipsCategory = BlogCategory::where('name', 'Tips Rental')->first();
        $wisataCategory = BlogCategory::where('name', 'Wisata')->first();
        $otomotifCategory = BlogCategory::where('name', 'Otomotif')->first();

        $posts = [
            [
                'title' => 'Tips Memilih Kendaraan Rental yang Tepat',
                'excerpt' => 'Panduan lengkap memilih kendaraan rental sesuai kebutuhan perjalanan Anda.',
                'content' => '<p>Memilih kendaraan rental yang tepat sangat penting untuk kenyamanan perjalanan. Berikut beberapa tips yang bisa membantu Anda:</p><h3>1. Tentukan Kebutuhan</h3><p>Apakah Anda bepergian sendiri, berdua, atau bersama keluarga besar? Jumlah penumpang akan menentukan jenis kendaraan yang cocok.</p><h3>2. Perhatikan Jenis Medan</h3><p>Jika akan melewati medan pegunungan, SUV atau MPV dengan mesin bertenaga bisa menjadi pilihan terbaik.</p><h3>3. Sesuaikan dengan Budget</h3><p>Tentukan budget harian untuk rental dan pilih kendaraan yang sesuai. Jangan lupa untuk memperhitungkan biaya bahan bakar.</p>',
                'category_id' => $tipsCategory?->id,
                'status' => 'published',
                'published_at' => now()->subDays(5),
                'meta_title' => 'Tips Memilih Kendaraan Rental - RentalMobil',
                'meta_description' => 'Panduan lengkap memilih kendaraan rental sesuai kebutuhan perjalanan Anda.',
            ],
            [
                'title' => 'Wisata Jalur Pantura: Jakarta ke Semarang',
                'excerpt' => 'Jelajahi keindahan jalur pantai utara Jawa dari Jakarta ke Semarang.',
                'content' => '<p>Jalur Pantura (Pantai Utara Jawa) menawarkan perjalanan yang menarik dari Jakarta ke Semarang. Berikut rekomendasi perjalanan:</p><h3>Hari 1: Jakarta - Cirebon</h3><p>Perjalanan sekitar 3-4 jam. Singgah di Keraton Kasepuhan dan nikmati kuliner empal gentong.</p><h3>Hari 2: Cirebon - Semarang</h3><p>Lanjutkan perjalanan ke Semarang. Kunjungi Lawang Sewu dan kota lama Semarang.</p>',
                'category_id' => $wisataCategory?->id,
                'status' => 'published',
                'published_at' => now()->subDays(3),
                'meta_title' => 'Wisata Jalur Pantura - RentalMobil',
                'meta_description' => 'Jelajahi keindahan jalur pantai utara Jawa dari Jakarta ke Semarang.',
            ],
            [
                'title' => 'Perawatan Mobil Sebelum Perjalanan Jauh',
                'excerpt' => 'Checklist perawatan mobil wajib sebelum memulai perjalanan jauh.',
                'content' => '<p>Sebelum memulai perjalanan jauh, pastikan kendaraan dalam kondisi prima. Berikut checklist yang perlu diperhatikan:</p><h3>1. Cek Oli Mesin</h3><p>Pastikan level oli mesin masih dalam batas normal. Ganti oli jika sudah melewati jarak tempuh yang direkomendasikan.</p><h3>2. Cek Tekanan Ban</h3><p>Tekanan ban yang tepat sangat penting untuk keselamatan dan efisiensi bahan bakar.</p><h3>3. Cek Sistem Rem</h3><p>Pastikan sistem rem berfungsi dengan baik. Periksa ketebalan kampas rem dan cairan rem.</p>',
                'category_id' => $otomotifCategory?->id,
                'status' => 'published',
                'published_at' => now()->subDay(),
                'meta_title' => 'Perawatan Mobil Sebelum Perjalanan Jauh - RentalMobil',
                'meta_description' => 'Checklist perawatan mobil wajib sebelum memulai perjalanan jauh.',
            ],
            [
                'title' => 'Rental Mobil untuk Liburan Keluarga',
                'excerpt' => 'Rekomendasi kendaraan rental terbaik untuk liburan keluarga.',
                'content' => '<p>Liburan keluarga akan lebih menyenangkan dengan kendaraan yang nyaman. Berikut rekomendasi kami:</p><h3>MPV: Toyota Innova</h3><p>Kapasitas 7 penumpang, bagasi luas, dan suspensi nyaman untuk perjalanan jauh.</p><h3>SUV: Honda HR-V</h3><p>Cocok untuk medan yang bervariasi dengan fitur keselamatan lengkap.</p>',
                'category_id' => $tipsCategory?->id,
                'status' => 'published',
                'published_at' => now()->subDays(7),
                'meta_title' => 'Rental Mobil Liburan Keluarga - RentalMobil',
                'meta_description' => 'Rekomendasi kendaraan rental terbaik untuk liburan keluarga.',
            ],
            [
                'title' => 'Keunggulan Mobil Listrik untuk Perjalanan Kota',
                'excerpt' => 'Mengapa mobil listrik menjadi pilihan cerdas untuk perjalanan dalam kota.',
                'content' => '<p>Mobil listrik menawarkan banyak keunggulan untuk perjalanan dalam kota. Berikut alasannya:</p><h3>Hemat Biaya</h3><p>Biaya listrik jauh lebih murah dibandingkan bahan bakar fosil. Penghematan bisa mencapai 70%.</p><h3>Ramah Lingkungan</h3><p>Tidak ada emisi gas buang yang mencemari udara kota.</p><h3>Perawatan Minimal</h3><p>Mesin elektrik memiliki komponen yang lebih sedikit dibandingkan mesin konvensional.</p>',
                'category_id' => $otomotifCategory?->id,
                'status' => 'published',
                'published_at' => now()->subDays(10),
                'meta_title' => 'Keunggulan Mobil Listrik - RentalMobil',
                'meta_description' => 'Mengapa mobil listrik menjadi pilihan cerdas untuk perjalanan dalam kota.',
            ],
        ];

        foreach ($posts as $post) {
            BlogPost::updateOrCreate(
                ['title' => $post['title']],
                array_merge($post, [
                    'author_id' => $author?->id,
                    'is_featured' => false,
                    'views_count' => rand(50, 500),
                ])
            );
        }
    }

    protected function seedFaqs(): void
    {
        $faqs = [
            ['question' => 'Bagaimana cara menyewa mobil?', 'answer' => 'Pilih kendaraan yang diinginkan, tentukan tanggal dan lokasi pengambilan, lalu lakukan pemesanan melalui website atau WhatsApp kami. Tim kami akan mengkonfirmasi pesanan Anda.', 'category' => 'Pemesanan', 'sort_order' => 1],
            ['question' => 'Dokumen apa saja yang diperlukan untuk menyewa mobil?', 'answer' => 'Anda perlu menyiapkan KTP asli, SIM A yang masih berlaku, dan kartu kredit (untuk deposit). Untuk pelanggan korporat, diperlukan juga surat keterangan perusahaan.', 'category' => 'Pemesanan', 'sort_order' => 2],
            ['question' => 'Apakah bisa menyewa mobil dengan supir?', 'answer' => 'Ya, kami menyediakan layanan supir berpengalaman. Biaya supir adalah Rp 150.000/hari. Supir kami sudah berpengalaman dan mengetahui rute perjalanan dengan baik.', 'category' => 'Layanan', 'sort_order' => 3],
            ['question' => 'Bagaimana jika mobil mengalami kerusakan saat disewa?', 'answer' => 'Segera hubungi tim customer service kami. Kami akan memberikan bantuan darurat dan penggantian kendaraan jika diperlukan. Asuransi all-risk tersedia untuk perlindungan optimal.', 'category' => 'Layanan', 'sort_order' => 4],
            ['question' => 'Bisakah saya mengembalikan mobil di lokasi yang berbeda?', 'answer' => 'Ya, kami menyediakan layanan one-way rental dengan biaya tambahan. Silakan hubungi kami untuk informasi lebih lanjut mengenai biaya dan ketersediaan.', 'category' => 'Pengembalian', 'sort_order' => 5],
            ['question' => 'Apa yang terjadi jika saya terlambat mengembalikan mobil?', 'answer' => 'Biaya keterlambatan dikenakan per jam sesuai ketentuan. Jika terlambat lebih dari 24 jam, status pesanan akan diubah menjadi overdue dan akan ada biaya tambahan.', 'category' => 'Pengembalian', 'sort_order' => 6],
            ['question' => 'Bagaimana cara pembayaran?', 'answer' => 'Kami menerima pembayaran melalui transfer bank (BCA, Mandiri, BRI), tunai saat pengambilan kendaraan, dan QRIS. Pembayaran deposit diperlukan saat pemesanan.', 'category' => 'Pembayaran', 'sort_order' => 7],
            ['question' => 'Apakah harga sudah termasuk BBM?', 'answer' => 'Harga rental belum termasuk BBM. Kendaraan dikirim dengan kondisi BBM penuh dan harus dikembalikan dalam kondisi yang sama.', 'category' => 'Pembayaran', 'sort_order' => 8],
            ['question' => 'Bagaimana jika saya ingin membatalkan pesanan?', 'answer' => 'Pembatalan yang dilakukan lebih dari 48 jam sebelum jadwal pengambilan akan mendapat pengembalian deposit penuh. Pembatalan kurang dari 48 jam dikenakan biaya pembatalan 50%.', 'category' => 'Pembatalan', 'sort_order' => 9],
            ['question' => 'Apakah ada batasan kilometer?', 'answer' => 'Tidak ada batasan kilometer untuk rental harian dan mingguan. Namun, untuk rental bulanan, batas normal adalah 4.000 km/bulan. Kelebihan akan dikenakan biaya Rp 2.000/km.', 'category' => 'Ketentuan', 'sort_order' => 10],
        ];

        foreach ($faqs as $faq) {
            Faq::updateOrCreate(
                ['question' => $faq['question']],
                array_merge($faq, ['is_active' => true])
            );
        }
    }

    protected function seedSystemSettings(): void
    {
        $settings = [
            ['group' => 'general', 'key' => 'app_name', 'value' => 'RentalMobil', 'type' => 'string', 'label' => 'Nama Aplikasi', 'description' => 'Nama aplikasi yang ditampilkan', 'sort_order' => 1],
            ['group' => 'general', 'key' => 'company_name', 'value' => 'PT RentalMobil Indonesia', 'type' => 'string', 'label' => 'Nama Perusahaan', 'description' => 'Nama resmi perusahaan', 'sort_order' => 2],
            ['group' => 'general', 'key' => 'company_address', 'value' => 'Jl. Sudirman No. 123, Jakarta Pusat', 'type' => 'string', 'label' => 'Alamat Perusahaan', 'description' => 'Alamat kantor pusat', 'sort_order' => 3],
            ['group' => 'general', 'key' => 'company_phone', 'value' => '021-555-0101', 'type' => 'string', 'label' => 'Telepon Perusahaan', 'description' => 'Nomor telepon kantor', 'sort_order' => 4],
            ['group' => 'general', 'key' => 'company_email', 'value' => 'info@rentalmobil.test', 'type' => 'string', 'label' => 'Email Perusahaan', 'description' => 'Email resmi perusahaan', 'sort_order' => 5],
            ['group' => 'finance', 'key' => 'currency', 'value' => 'IDR', 'type' => 'string', 'label' => 'Mata Uang', 'description' => 'Kode mata uang', 'sort_order' => 10],
            ['group' => 'finance', 'key' => 'currency_symbol', 'value' => 'Rp', 'type' => 'string', 'label' => 'Simbol Mata Uang', 'description' => 'Simbol mata uang', 'sort_order' => 11],
            ['group' => 'finance', 'key' => 'tax_rate', 'value' => '0.11', 'type' => 'float', 'label' => 'Tarif Pajak', 'description' => 'Tarif PPN (11%)', 'sort_order' => 12],
            ['group' => 'finance', 'key' => 'tax_name', 'value' => 'PPN', 'type' => 'string', 'label' => 'Nama Pajak', 'description' => 'Nama pajak yang dikenakan', 'sort_order' => 13],
            ['group' => 'rental', 'key' => 'overdue_threshold_hours', 'value' => '24', 'type' => 'integer', 'label' => 'Batas Overdue (jam)', 'description' => 'Jam sebelum status berubah ke overdue', 'sort_order' => 20],
            ['group' => 'rental', 'key' => 'missing_threshold_hours', 'value' => '72', 'type' => 'integer', 'label' => 'Batas Missing (jam)', 'description' => 'Jam sebelum eskalasi ke missing', 'sort_order' => 21],
            ['group' => 'rental', 'key' => 'reminder_grace_hours', 'value' => '2', 'type' => 'integer', 'label' => 'Grace Period Reminder (jam)', 'description' => 'Jam grace period sebelum reminder dikirim', 'sort_order' => 22],
            ['group' => 'rental', 'key' => 'driver_daily_cost', 'value' => '150000', 'type' => 'integer', 'label' => 'Biaya Supir/Hari', 'description' => 'Biaya harian untuk layanan supir', 'sort_order' => 23],
            ['group' => 'rental', 'key' => 'invoice_due_days', 'value' => '7', 'type' => 'integer', 'label' => 'Jatuh Tempo Invoice (hari)', 'description' => 'Hari jatuh tempo invoice dari tanggal sewa', 'sort_order' => 24],
            ['group' => 'rental', 'key' => 'km_limit_monthly', 'value' => '4000', 'type' => 'integer', 'label' => 'Batas KM Bulanan', 'description' => 'Batas kilometer normal per bulan', 'sort_order' => 25],
            ['group' => 'rental', 'key' => 'km_overage_rate', 'value' => '2000', 'type' => 'integer', 'label' => 'Tarif Kelebihan KM', 'description' => 'Biaya per km untuk kelebihan kilometer', 'sort_order' => 26],
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
            ['code' => '1100', 'name' => 'Kas', 'type' => 'asset', 'normal_balance' => 'debit', 'is_system' => true],
            ['code' => '1110', 'name' => 'Bank BCA', 'type' => 'asset', 'normal_balance' => 'debit', 'is_system' => true],
            ['code' => '1120', 'name' => 'Bank Mandiri', 'type' => 'asset', 'normal_balance' => 'debit', 'is_system' => true],
            ['code' => '1200', 'name' => 'Piutang Usaha', 'type' => 'asset', 'normal_balance' => 'debit', 'is_system' => true],
            ['code' => '1300', 'name' => 'Persediaan', 'type' => 'asset', 'normal_balance' => 'debit', 'is_system' => true],
            ['code' => '1500', 'name' => 'Kendaraan', 'type' => 'asset', 'normal_balance' => 'debit', 'is_system' => true],
            ['code' => '1510', 'name' => 'Akumulasi Depresiasi', 'type' => 'asset', 'normal_balance' => 'credit', 'is_system' => true],
            ['code' => '2100', 'name' => 'Hutang Usaha', 'type' => 'liability', 'normal_balance' => 'credit', 'is_system' => true],
            ['code' => '2200', 'name' => 'Hutang Pajak', 'type' => 'liability', 'normal_balance' => 'credit', 'is_system' => true],
            ['code' => '2300', 'name' => 'PPN Masukan', 'type' => 'liability', 'normal_balance' => 'credit', 'is_system' => true],
            ['code' => '3100', 'name' => 'Modal Disetor', 'type' => 'equity', 'normal_balance' => 'credit', 'is_system' => true],
            ['code' => '3200', 'name' => 'Laba Ditahan', 'type' => 'equity', 'normal_balance' => 'credit', 'is_system' => true],
            ['code' => '4100', 'name' => 'Pendapatan Sewa', 'type' => 'revenue', 'normal_balance' => 'credit', 'is_system' => true],
            ['code' => '4200', 'name' => 'Pendapatan Supir', 'type' => 'revenue', 'normal_balance' => 'credit', 'is_system' => true],
            ['code' => '4300', 'name' => 'Pendapatan Addon', 'type' => 'revenue', 'normal_balance' => 'credit', 'is_system' => true],
            ['code' => '4400', 'name' => 'Pendapatan Denda', 'type' => 'revenue', 'normal_balance' => 'credit', 'is_system' => true],
            ['code' => '5100', 'name' => 'Biaya BBM', 'type' => 'expense', 'normal_balance' => 'debit', 'is_system' => true],
            ['code' => '5200', 'name' => 'Biaya Perawatan', 'type' => 'expense', 'normal_balance' => 'debit', 'is_system' => true],
            ['code' => '5300', 'name' => 'Biaya Gaji Supir', 'type' => 'expense', 'normal_balance' => 'debit', 'is_system' => true],
            ['code' => '5400', 'name' => 'Biaya Asuransi', 'type' => 'expense', 'normal_balance' => 'debit', 'is_system' => true],
            ['code' => '5500', 'name' => 'Biaya Sewa Tempat', 'type' => 'expense', 'normal_balance' => 'debit', 'is_system' => true],
            ['code' => '5600', 'name' => 'Biaya Depresiasi', 'type' => 'expense', 'normal_balance' => 'debit', 'is_system' => true],
        ];

        foreach ($accounts as $account) {
            ChartOfAccount::updateOrCreate(
                ['code' => $account['code']],
                array_merge($account, [
                    'is_active' => true,
                ])
            );
        }
    }
}

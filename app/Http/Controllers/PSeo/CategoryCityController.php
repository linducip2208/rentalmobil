<?php

namespace App\Http\Controllers\PSeo;

class CategoryCityController extends BasePseoController
{
    protected array $cityDescriptions = [
        'Jakarta' => 'Jakarta sebagai ibu kota Indonesia memiliki permintaan rental mobil tertinggi. Dari kebutuhan antar-jemput bandara, perjalanan bisnis di CBD, hingga weekend getaway ke Puncak atau Bandung. Armada kami di Jakarta tersedia lengkap dari city car hingga bus pariwisata.',
        'Bandung' => 'Bandung menjadi destinasi populer untuk wisata kuliner dan belanja. Menyewa mobil di Bandung memudahkan Anda menjelajahi Lembang, Ciwidey, hingga Tangkuban Perahu tanpa batasan jadwal transportasi umum.',
        'Surabaya' => 'Surabaya sebagai kota terbesar kedua di Indonesia memiliki kebutuhan rental mobil yang tinggi untuk bisnis dan wisata. Akses mudah ke Malang, Bromo, dan Banyuwangi menjadikan sewa mobil pilihan terbaik.',
        'Bali' => 'Bali adalah surga wisata Indonesia. Menyewa mobil di Bali memberikan kebebasan menjelajahi Ubud, Kuta, Seminyak, hingga Nusa Penida. Tersedia driver berpengalaman yang hafal setiap sudut pulau.',
        'Yogyakarta' => 'Yogyakarta kota pelajar dan budaya. Sewa mobil di Jogja memudahkan akses ke Borobudur, Prambanan, Malioboro, hingga wisata alam Gunung Kidul dan Kulon Progo.',
        'Medan' => 'Medan adalah gerbang ke Sumatera Utara. Sewa mobil di Medan memudahkan perjalanan ke Danau Toba, Bukit Lawang, hingga Tanah Karo.',
    ];

    public function __invoke(string $city)
    {
        $cityTitle = ucwords(str_replace('-', ' ', $city));
        $cityDescription = $this->cityDescriptions[$cityTitle] ?? "Menyewa mobil di {$cityTitle} memberikan Anda kebebasan menjelajahi kota dan sekitarnya. Kami menyediakan armada lengkap dengan kondisi terawat dan asuransi all-risk.";

        $jsonLd = $this->jsonLdLocalBusiness($cityTitle);

        return view('pseo.category-city', array_merge(
            $this->seoMeta(
                "Sewa Mobil di {$cityTitle} — Harga Terbaik | RentalMobil",
                "Sewa mobil murah dan terpercaya di {$cityTitle}. Katalog lengkap, harga transparan, booking online 24/7.",
                url("/sewa-mobil-di-{$city}"),
                $jsonLd
            ),
            [
                'city' => $cityTitle,
                'citySlug' => $city,
                'cityDescription' => $cityDescription,
                'vehicles' => [],
            ]
        ));
    }
}

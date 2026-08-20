<?php

namespace App\Http\Controllers\PSeo;

class VehicleDetailController extends BasePseoController
{
    public function __invoke(string $vehicleSlug)
    {
        $vehicleName = ucwords(str_replace('-', ' ', $vehicleSlug));

        $vehicle = (object) [
            'name' => $vehicleName,
            'slug' => $vehicleSlug,
            'category' => 'SUV',
            'year' => '2024',
            'transmission' => 'Automatic',
            'fuel' => 'Bensin',
            'seats' => '7',
            'luggage' => '3 koper',
            'mileage' => '< 50.000 km',
            'price_per_day' => 500000,
            'description' => $vehicleName . ' merupakan pilihan ideal untuk perjalanan Anda. Dalam kondisi terawat, dilengkapi asuransi all-risk, dan siap menemani perjalanan di dalam maupun luar kota.',
        ];

        $jsonLd = $this->jsonLdProduct([
            'name' => $vehicle->name,
            'description' => $vehicle->description,
            'price' => $vehicle->price_per_day,
            'brand' => 'RentalMobil',
            'url' => url("/sewa/{$vehicleSlug}"),
        ]);

        return view('pseo.vehicle-detail', array_merge(
            $this->seoMeta(
                "Sewa {$vehicleName} — Harga & Spesifikasi | RentalMobil",
                "Sewa {$vehicleName} dengan harga terbaik. Spesifikasi lengkap, asuransi all-risk, booking online 24/7.",
                url("/sewa/{$vehicleSlug}"),
                $jsonLd
            ),
            [
                'vehicle' => $vehicle,
                'relatedVehicles' => [],
            ]
        ));
    }
}

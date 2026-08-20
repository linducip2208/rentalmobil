<?php

namespace App\Http\Controllers\PSeo;

class CompareController extends BasePseoController
{
    public function __invoke(string $a, string $b)
    {
        $vehicleA = ucwords(str_replace('-', ' ', $a));
        $vehicleB = ucwords(str_replace('-', ' ', $b));

        $jsonLd = $this->jsonLdItemList(
            [
                ['name' => $vehicleA, 'slug' => $a],
                ['name' => $vehicleB, 'slug' => $b],
            ],
            "Perbandingan {$vehicleA} vs {$vehicleB}",
            url("/bandingkan/{$a}-vs-{$b}")
        );

        return view('pseo.compare', array_merge(
            $this->seoMeta(
                "{$vehicleA} vs {$vehicleB} — Perbandingan Lengkap | RentalMobil",
                "Perbandingan lengkap {$vehicleA} vs {$vehicleB}. Spesifikasi, harga, kelebihan dan kekurangan masing-masing.",
                url("/bandingkan/{$a}-vs-{$b}"),
                $jsonLd
            ),
            [
                'vehicleA' => $vehicleA,
                'vehicleB' => $vehicleB,
                'slugA' => $a,
                'slugB' => $b,
                'specA' => [],
                'specB' => [],
                'verdict' => null,
            ]
        ));
    }
}

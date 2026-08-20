<?php

namespace App\Http\Controllers\PSeo;

class AlternativeController extends BasePseoController
{
    public function __invoke(string $slug)
    {
        $vehicleName = ucwords(str_replace('-', ' ', $slug));

        $jsonLd = $this->jsonLdItemList(
            [],
            "Alternatif {$vehicleName}",
            url("/alternatives-to-{$slug}")
        );

        return view('pseo.alternatives', array_merge(
            $this->seoMeta(
                "Alternatif {$vehicleName} — Pilihan Kendaraan Serupa | RentalMobil",
                "Mencari alternatif {$vehicleName}? Lihat daftar kendaraan serupa yang tersedia di RentalMobil.",
                url("/alternatives-to-{$slug}"),
                $jsonLd
            ),
            [
                'vehicleName' => $vehicleName,
                'slug' => $slug,
                'intro' => null,
                'alternatives' => [],
            ]
        ));
    }
}

<?php

namespace App\Services\Gps\Contracts;

use App\Models\GpsIntegration;

interface GpsAdapter
{
    public function test(GpsIntegration $integration): array;

    public function pullPositions(GpsIntegration $integration): array;
}

<?php

namespace App\Http\Controllers\PSeo;

use App\Http\Controllers\StorefrontController;
use App\Models\Vehicle;
use App\Services\AvailabilityEngine;

/**
 * Legacy vehicle detail route (/sewa/{slug}).
 *
 * Now backed by real database vehicles and rendered through the same
 * storefront detail view as the canonical /mobil/{slug} route.
 */
class VehicleDetailController extends BasePseoController
{
    public function __invoke(Vehicle $vehicle, StorefrontController $storefront)
    {
        if (! $vehicle->is_active) {
            abort(404);
        }

        return $storefront->show(request(), $vehicle, app(AvailabilityEngine::class));
    }
}

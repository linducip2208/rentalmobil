<?php

namespace App\Services\Gps;

use App\Models\GpsIntegration;
use App\Services\Gps\Contracts\GpsAdapter;

class GpsAdapterManager
{
    public function for(GpsIntegration $integration): GpsAdapter
    {
        return match ($integration->adapter_format) {
            'rest_polling', 'traccar_compatible' => app(RestPollingGpsAdapter::class),
            default => throw new \RuntimeException("Format adapter '{$integration->adapter_format}' tidak mendukung polling."),
        };
    }
}

<?php

return [
    'offline_threshold_minutes' => (int) env('GPS_OFFLINE_THRESHOLD_MINUTES', 15),
    'log_retention_days' => (int) env('GPS_LOG_RETENTION_DAYS', 180),
];

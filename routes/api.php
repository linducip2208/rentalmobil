<?php

use App\Http\Controllers\Api\GpsTrackingController;
use App\Http\Controllers\Api\GenericGpsWebhookController;
use Illuminate\Support\Facades\Route;

Route::post('/gps/report', [GpsTrackingController::class, 'reportLocation']);
Route::post('/gps/webhook/{integration}', GenericGpsWebhookController::class)->middleware('throttle:300,1');
Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
    Route::get('/gps/trackers', [GpsTrackingController::class, 'getActiveTrackers']);
    Route::get('/gps/vehicles', [GpsTrackingController::class, 'getActiveVehicles']);
});

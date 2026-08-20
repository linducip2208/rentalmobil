<?php

use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('vehicles', fn () => response()->json(['message' => 'Vehicle list endpoint']));
    Route::get('vehicles/{vehicle:slug}', fn () => response()->json(['message' => 'Vehicle detail endpoint']));
    Route::post('vehicles/check-availability', fn () => response()->json(['message' => 'Availability check endpoint']));

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('customer/profile', fn () => response()->json(['message' => 'Customer profile']));
        Route::get('customer/orders', fn () => response()->json(['message' => 'Customer orders']));
        Route::get('bookings', fn () => response()->json(['message' => 'Bookings list']));
        Route::post('bookings', fn () => response()->json(['message' => 'Create booking']));
    });
});

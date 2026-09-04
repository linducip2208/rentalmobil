<?php

use App\Http\Controllers\AdminLTE\BookingController;
use App\Http\Controllers\AdminLTE\CustomerController;
use App\Http\Controllers\AdminLTE\DashboardController;
use App\Http\Controllers\AdminLTE\DriverController;
use App\Http\Controllers\AdminLTE\InvoiceController;
use App\Http\Controllers\AdminLTE\OrderController;
use App\Http\Controllers\AdminLTE\PaymentController;
use App\Http\Controllers\AdminLTE\VehicleController;
use App\Http\Middleware\EnsureAdminRoleAccess;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', EnsureAdminRoleAccess::class])
    ->prefix('lte')
    ->name('lte.')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::post('/logout', function () {
            Auth::guard('web')->logout();

            return redirect()->route('filament.admin.auth.login');
        })->name('logout');

        Route::resource('vehicles', VehicleController::class)->except(['show'])->names('vehicles');
        Route::get('bookings', [BookingController::class, 'index'])->name('bookings.index');
        Route::get('bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
        Route::post('bookings/{booking}/confirm', [BookingController::class, 'confirm'])->name('bookings.confirm');
        Route::post('bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');
        Route::post('bookings/{booking}/convert', [BookingController::class, 'convert'])->name('bookings.convert');

        Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::post('orders/{order}/deposits/{deposit}/refund', [OrderController::class, 'refundDeposit'])->name('orders.refund-deposit');

        Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::post('payments/{payment}/verify', [PaymentController::class, 'verify'])->name('payments.verify');
        Route::post('payments/{payment}/reject', [PaymentController::class, 'reject'])->name('payments.reject');

        Route::get('drivers', [DriverController::class, 'index'])->name('drivers.index');
        Route::get('drivers/{driver}', [DriverController::class, 'show'])->name('drivers.show');
        Route::post('drivers/{driver}/toggle-availability', [DriverController::class, 'toggleAvailability'])->name('drivers.toggle');

        Route::get('customers', [CustomerController::class, 'index'])->name('customers.index');
        Route::get('customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');

        Route::get('invoices', [InvoiceController::class, 'index'])->name('invoices.index');
        Route::get('invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
    });

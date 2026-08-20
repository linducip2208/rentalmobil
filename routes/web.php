<?php

use App\Http\Controllers\BlogController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DocsController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\PSeo\AlternativeController;
use App\Http\Controllers\PSeo\CategoryCityController;
use App\Http\Controllers\PSeo\CategoryListController;
use App\Http\Controllers\PSeo\CompareController;
use App\Http\Controllers\PSeo\VehicleDetailController;
use App\Http\Controllers\ProgrammaticSeoController;
use App\Http\Controllers\RobotsController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect('/admin');
    }
    return response()->view('marketing.home');
})->name('home');

Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/feed.xml', [BlogController::class, 'feed'])->name('blog.feed');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');

Route::get('/contact', [ContactController::class, 'show'])->name('contact.show');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');

Route::get('/faq', [FaqController::class, 'index'])->name('faq.index');

Route::get('/docs', [DocsController::class, 'index'])->name('docs.index');

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/sitemap/{section}-{page}.xml', [SitemapController::class, 'show'])->where(['section' => 'core|custom', 'page' => '[1-9][0-9]*'])->name('sitemap.chunk');
Route::get('/robots.txt', [RobotsController::class, 'index'])->name('robots');

Route::get('/health', fn () => response()->json(['status' => 'ok']));

Route::middleware('auth')->get('/internal/gps/trackers', [\App\Http\Controllers\Api\GpsTrackingController::class, 'getActiveTrackers'])
    ->name('internal.gps.trackers');

Route::prefix('portal')->name('portal.')->group(function () {
    Route::middleware('guest:customer')->group(function () {
        Route::get('/login', [\App\Http\Controllers\Portal\AuthController::class, 'create'])->name('login');
        Route::post('/login', [\App\Http\Controllers\Portal\AuthController::class, 'store'])->middleware('throttle:5,1')->name('login.store');
    });
    Route::middleware('auth:customer')->group(function () {
        Route::get('/', [\App\Http\Controllers\Portal\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/pesanan', [\App\Http\Controllers\Portal\DashboardController::class, 'orders'])->name('orders');
        Route::get('/invoice', [\App\Http\Controllers\Portal\DashboardController::class, 'invoices'])->name('invoices');
        Route::get('/invoice/{invoice}/download', [\App\Http\Controllers\Portal\DashboardController::class, 'downloadInvoice'])->name('invoices.download');
        Route::post('/invoice/{invoice}/bukti-bayar', [\App\Http\Controllers\Portal\DashboardController::class, 'uploadPaymentProof'])->middleware('throttle:10,1')->name('invoices.payment-proof');
        Route::post('/logout', [\App\Http\Controllers\Portal\AuthController::class, 'destroy'])->name('logout');
    });
});

Route::get('/sewa-mobil', [CategoryListController::class, 'index'])->name('pseo.category-list');
Route::get('/sewa-mobil-di-{city}', CategoryCityController::class)->name('pseo.category-city');
Route::get('/sewa/{vehicle}', VehicleDetailController::class)->name('pseo.vehicle-detail');
Route::get('/bandingkan/{a}-vs-{b}', CompareController::class)->name('pseo.compare');
Route::get('/alternatives-to-{slug}', AlternativeController::class)->name('pseo.alternatives');

Route::get('/best-{category}', [ProgrammaticSeoController::class, 'bestCategory'])->name('pseo.best-category');
Route::get('/best-{category}-{year}', [ProgrammaticSeoController::class, 'bestCategory'])->name('pseo.best-category-year');
Route::get('/compare/{a}-vs-{b}', [ProgrammaticSeoController::class, 'compareVehicles'])->name('pseo.compare-en');
Route::get('/beli-aplikasi-rental-mobil', [ProgrammaticSeoController::class, 'sourceCode'])->name('pseo.source-code');
Route::get('/source-code-rental-mobil', [ProgrammaticSeoController::class, 'sourceCode'])->name('pseo.source-code-alt');

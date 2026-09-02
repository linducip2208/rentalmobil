<?php

use App\Http\Controllers\Api\GpsTrackingController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\CmsPageController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DocsController;
use App\Http\Controllers\EmbedBookingController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\PaymentCallbackController;
use App\Http\Controllers\Portal\AuthController;
use App\Http\Controllers\Portal\DashboardController;
use App\Http\Controllers\ProgrammaticSeoController;
use App\Http\Controllers\PSeo\AlternativeController;
use App\Http\Controllers\PSeo\CategoryCityController;
use App\Http\Controllers\PSeo\CompareController;
use App\Http\Controllers\PSeo\VehicleDetailController;
use App\Http\Controllers\PublicBookingController;
use App\Http\Controllers\PublicHandoverController;
use App\Http\Controllers\RobotsController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\StorefrontController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CmsPageController::class, 'home'])->name('home');

// ===== Storefront rental =====
Route::get('/sewa-mobil', [StorefrontController::class, 'catalog'])->name('storefront.catalog');
Route::get('/sewa-mobil/cari', [StorefrontController::class, 'search'])->name('storefront.search');
Route::get('/sewa-mobil/{categorySlug}', [StorefrontController::class, 'catalog'])
    ->where('categorySlug', '[a-z0-9-]+')
    ->name('storefront.category');
Route::get('/mobil/{vehicle:slug}', [StorefrontController::class, 'show'])->name('storefront.show');
Route::get('/lokasi', [StorefrontController::class, 'locations'])->name('storefront.locations');
Route::get('/cara-sewa', fn () => view('storefront.how-it-works'))->name('storefront.how-it-works');

Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/feed.xml', [BlogController::class, 'feed'])->name('blog.feed');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');

Route::get('/contact', [ContactController::class, 'show'])->name('contact.show');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');

Route::get('/faq', [FaqController::class, 'index'])->name('faq.index');

Route::get('/docs', [DocsController::class, 'index'])->name('docs.index');
Route::get('/corporate', [CmsPageController::class, 'corporate'])->name('corporate');
Route::get('/tentang-kami', [CmsPageController::class, 'about'])->name('about');

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/sitemap/{section}-{page}.xml', [SitemapController::class, 'show'])->where(['section' => 'core|custom', 'page' => '[1-9][0-9]*'])->name('sitemap.chunk');
Route::get('/robots.txt', [RobotsController::class, 'index'])->name('robots');

Route::get('/health', fn () => response()->json(['status' => 'ok']));
Route::get('/booking', [PublicBookingController::class, 'index'])->name('booking.index');
Route::post('/booking/quote', [PublicBookingController::class, 'quote'])->middleware('throttle:30,1')->name('booking.quote');
Route::post('/booking', [PublicBookingController::class, 'store'])->middleware('throttle:10,1')->name('booking.store');
Route::get('/booking/{booking}/berhasil', [PublicBookingController::class, 'success'])->middleware('signed')->name('booking.success');
Route::post('/payments/callback/{provider}', PaymentCallbackController::class)->middleware('throttle:300,1')->name('payments.callback');

// ===== Embeddable booking widget (mitra) + API ketersediaan publik =====
Route::get('/embed/booking', fn () => response()->view('embed.booking'))->name('embed.booking');
Route::get('/api/public/availability', [EmbedBookingController::class, 'availability'])->middleware('throttle:60,1')->name('api.public.availability');
Route::get('/api/public/meta', [EmbedBookingController::class, 'meta'])->middleware('throttle:60,1')->name('api.public.meta');
Route::post('/booking/hold', [PublicBookingController::class, 'hold'])->middleware('throttle:20,1')->name('booking.hold');

Route::get('/handover/kontrak/{token}', [PublicHandoverController::class, 'showContract'])->name('handover.contract.show');
Route::post('/handover/kontrak/{token}/otp', [PublicHandoverController::class, 'sendOtp'])->middleware('throttle:5,1')->name('handover.contract.otp');
Route::post('/handover/kontrak/{token}', [PublicHandoverController::class, 'signContract'])->middleware('throttle:10,1')->name('handover.contract.sign');
Route::get('/handover/checkin/{token}', [PublicHandoverController::class, 'showCheckIn'])->name('handover.checkin.show');
Route::post('/handover/checkin/{token}', [PublicHandoverController::class, 'submitCheckIn'])->middleware('throttle:10,1')->name('handover.checkin.submit');

Route::middleware('auth')->get('/internal/gps/trackers', [GpsTrackingController::class, 'getActiveTrackers'])
    ->name('internal.gps.trackers');

Route::prefix('portal')->name('portal.')->group(function () {
    Route::middleware('guest:customer')->group(function () {
        Route::get('/login', [AuthController::class, 'create'])->name('login');
        Route::post('/login', [AuthController::class, 'store'])->middleware('throttle:5,1')->name('login.store');
    });
    Route::middleware('auth:customer')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/pesanan', [DashboardController::class, 'orders'])->name('orders');
        Route::get('/langganan', [DashboardController::class, 'subscriptions'])->name('subscriptions');
        Route::get('/inspeksi', [DashboardController::class, 'inspections'])->name('inspections');
        Route::post('/pesanan/{order}/jadwal-ulang', [DashboardController::class, 'reschedule'])->middleware('throttle:5,1')->name('orders.reschedule');
        Route::get('/invoice', [DashboardController::class, 'invoices'])->name('invoices');
        Route::get('/invoice/{invoice}/download', [DashboardController::class, 'downloadInvoice'])->name('invoices.download');
        Route::post('/invoice/{invoice}/bukti-bayar', [DashboardController::class, 'uploadPaymentProof'])->middleware('throttle:10,1')->name('invoices.payment-proof');
        Route::post('/invoice/{invoice}/bayar', [DashboardController::class, 'checkoutPayment'])->middleware('throttle:10,1')->name('invoices.pay');
        Route::post('/pesanan/{order}/perpanjang', [DashboardController::class, 'requestExtension'])->middleware('throttle:5,1')->name('orders.extend');
        Route::post('/dokumen/{document}/unggah-ulang', [DashboardController::class, 'reuploadDocument'])->middleware('throttle:10,1')->name('documents.reupload');
        Route::get('/referral', [DashboardController::class, 'referrals'])->name('referrals');
        Route::get('/poin', [DashboardController::class, 'loyaltyPoints'])->name('loyalty');
        Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
    });
});

// Katalog legacy (pSEO) — /sewa-mobil sekarang menjadi katalog nyata di atas.
Route::get('/sewa-mobil-di-{city}', CategoryCityController::class)->name('pseo.category-city');
Route::get('/sewa/{vehicle:slug}', VehicleDetailController::class)->name('pseo.vehicle-detail');
Route::get('/bandingkan/{a}-vs-{b}', CompareController::class)->name('pseo.compare');
Route::get('/alternatives-to-{slug}', AlternativeController::class)->name('pseo.alternatives');

Route::get('/best-{category}-{year}', [ProgrammaticSeoController::class, 'bestCategory'])->name('pseo.best-category-year');
Route::get('/best-{category}', [ProgrammaticSeoController::class, 'bestCategory'])->name('pseo.best-category');
Route::get('/compare/{a}-vs-{b}', [ProgrammaticSeoController::class, 'compareVehicles'])->name('pseo.compare-en');
Route::get('/beli-aplikasi-rental-mobil', [ProgrammaticSeoController::class, 'sourceCode'])->name('pseo.source-code');
Route::get('/source-code-rental-mobil', [ProgrammaticSeoController::class, 'sourceCode'])->name('pseo.source-code-alt');

// Harus diletakkan paling akhir agar slug CMS tidak menimpa route bisnis/public lain.
Route::get('/{pageSlug}', [CmsPageController::class, 'show'])
    ->where('pageSlug', '[a-z0-9][a-z0-9-]*')
    ->name('cms.page');

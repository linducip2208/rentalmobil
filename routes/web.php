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
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect('/admin');
    }
    return view('marketing');
})->name('home');

Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');

Route::get('/contact', [ContactController::class, 'show'])->name('contact.show');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');

Route::get('/faq', [FaqController::class, 'index'])->name('faq.index');

Route::get('/docs', [DocsController::class, 'index'])->name('docs.index');

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

Route::get('/health', fn () => response()->json(['status' => 'ok']));

Route::get('/sewa-mobil', [CategoryListController::class, 'index'])->name('pseo.category-list');
Route::get('/sewa-mobil-di-{city}', CategoryCityController::class)->name('pseo.category-city');
Route::get('/sewa/{vehicle}', VehicleDetailController::class)->name('pseo.vehicle-detail');
Route::get('/bandingkan/{a}-vs-{b}', CompareController::class)->name('pseo.compare');
Route::get('/alternatives-to-{slug}', AlternativeController::class)->name('pseo.alternatives');

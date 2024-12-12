<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProfileCompletionController;
use App\Http\Controllers\RfqController;
use App\Http\Controllers\RfqQuoteController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\InspectionController;
use App\Http\Controllers\UserAnalyticsController;
use App\Http\Controllers\SupportTicketController;
use App\Http\Controllers\SupportTicketResponseController;
use App\Http\Controllers\ProductListingController;
use App\Http\Middleware\EnsureProfileIsComplete;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Public routes
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
})->name('welcome');

// Public SEO-friendly product listing routes
Route::get('sitemap.xml', [ProductListingController::class, 'generateSitemap'])->name('sitemap');
Route::get('products', [ProductListingController::class, 'index'])->name('products.listing.index');
Route::get('products/{category}', [ProductListingController::class, 'category'])
    ->where('category', '[a-z0-9-]+')
    ->name('products.listing.category');
Route::get('products/{category}/{country}', [ProductListingController::class, 'country'])
    ->where(['category' => '[a-z0-9-]+', 'country' => '[a-z0-9-]+'])
    ->name('products.listing.country');
Route::get('products/{category}/{country}/{slug}', [ProductListingController::class, 'show'])
    ->where([
        'category' => '[a-z0-9-]+',
        'country' => '[a-z0-9-]+',
        'slug' => '[a-z0-9-]+'
    ])
    ->name('products.listing.detail');

// Authentication routes
require __DIR__.'/auth.php';

// Protected routes
Route::middleware(['auth', 'verified'])->group(function () {
    // Profile completion routes
    Route::get('/profile/complete', [ProfileCompletionController::class, 'show'])
        ->name('profile.complete');
    Route::post('/profile/complete', [ProfileCompletionController::class, 'store'])
        ->name('profile.complete.store');

    // Routes that require completed profile
    Route::middleware(EnsureProfileIsComplete::class)->group(function () {
        Route::get('/dashboard', function () {
            return Inertia::render('Dashboard');
        })->name('dashboard');

        // Profile routes
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

        // Product management routes (CRUD operations)
        Route::resource('products/manage', ProductController::class)->names([
            'index' => 'products.manage.index',
            'create' => 'products.manage.create',
            'store' => 'products.manage.store',
            'show' => 'products.manage.show',
            'edit' => 'products.manage.edit',
            'update' => 'products.manage.update',
            'destroy' => 'products.manage.destroy',
        ]);
        Route::patch('/products/{product}/publish', [ProductController::class, 'publish'])->name('products.publish');

        // Rest of the authenticated routes...
    });
});

<?php

use App\Http\Controllers\Admin\ProductController as AdminProductController;
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
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\SupportTicketController;
use App\Http\Controllers\SupportTicketResponseController;
use App\Http\Controllers\ProductListingController;
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
    // Profile completion routes (must be accessible without completed profile)
    Route::controller(ProfileCompletionController::class)->group(function () {
        Route::get('/profile/complete', 'show')->name('profile.complete');
        Route::post('/profile/complete', 'store')->name('profile.complete.store');
    });

    // Routes that require completed profile
    Route::middleware(\App\Http\Middleware\EnsureProfileIsComplete::class)->group(function () {
        Route::get('/dashboard', function () {
            return Inertia::render('Dashboard');
        })->name('dashboard');

        // Profile routes
        Route::controller(ProfileController::class)->group(function () {
            Route::get('/profile', 'edit')->name('profile.edit');
            Route::patch('/profile', 'update')->name('profile.update');
            Route::delete('/profile', 'destroy')->name('profile.destroy');
        });

        // Admin routes
        Route::middleware(\App\Http\Middleware\Role::class . ':admin')
            ->prefix('admin')
            ->name('admin.')
            ->controller(AdminProductController::class)
            ->group(function () {
                Route::get('products', 'index')->name('products.index');
                Route::get('products/create', 'create')->name('products.create');
                Route::post('products', 'store')->name('products.store');
                Route::get('products/{product}/edit', 'edit')->name('products.edit');
                Route::put('products/{product}', 'update')->name('products.update');
                Route::delete('products/{product}', 'destroy')->name('products.destroy');
                Route::post('products/{product}/publish', 'publish')->name('products.publish');
                
                // Analytics routes
                Route::controller(AdminDashboardController::class)->group(function () {
                    Route::get('analytics', 'index')->name('analytics');
                    Route::get('analytics/export', 'export')->name('analytics.export');
                });
            });

        // RFQ routes
        Route::resource('rfqs', RfqController::class);
        Route::patch('/rfqs/{rfq}/close', [RfqController::class, 'close'])->name('rfqs.close');
        Route::resource('rfqs.quotes', RfqQuoteController::class)->shallow();

        // Message routes
        Route::resource('messages', MessageController::class);
        Route::get('messages/threads/{thread}', [MessageController::class, 'show'])->name('messages.threads.show');
        Route::post('messages/threads/{thread}/reply', [MessageController::class, 'reply'])->name('messages.threads.reply');
        Route::post('messages/threads/{thread}/mark-read', [MessageController::class, 'markAsRead'])->name('messages.threads.mark-read');

        // Order routes
        Route::resource('orders', OrderController::class);
        Route::post('orders/{order}/pay', [PaymentController::class, 'store'])->name('orders.payments.store');
        Route::post('orders/{order}/documents', [DocumentController::class, 'store'])->name('orders.documents.store');
        Route::get('orders/{order}/documents/{document}/download', [DocumentController::class, 'download'])->name('orders.documents.download');
        Route::post('orders/{order}/inspection', [InspectionController::class, 'store'])->name('orders.inspection.store');
        Route::patch('orders/{order}/inspection/{inspection}', [InspectionController::class, 'update'])->name('orders.inspection.update');

        // Analytics routes
        Route::get('analytics/user', [UserAnalyticsController::class, 'index'])->name('analytics.user');
        Route::get('analytics/export', [UserAnalyticsController::class, 'export'])->name('analytics.export');

        // Support routes
        Route::prefix('support')->name('support.')->group(function () {
            Route::get('/', [SupportTicketController::class, 'index'])->name('index');
            Route::get('tickets/create', [SupportTicketController::class, 'create'])->name('tickets.create');
            Route::post('tickets', [SupportTicketController::class, 'store'])->name('tickets.store');
            Route::get('tickets/{ticket}', [SupportTicketController::class, 'show'])->name('tickets.show');
            Route::patch('tickets/{ticket}', [SupportTicketController::class, 'update'])->name('tickets.update');
            
            // Support ticket responses
            Route::post('tickets/{ticket}/responses', [SupportTicketResponseController::class, 'store'])->name('tickets.responses.store');
            Route::get('tickets/{ticket}/responses/{response}/attachments/{attachment}', [SupportTicketResponseController::class, 'downloadAttachment'])
                ->name('tickets.responses.attachments.download');
            
            // Support ticket feedback
            Route::post('tickets/{ticket}/feedback', [SupportTicketController::class, 'storeFeedback'])->name('tickets.feedback.store');
            
            // FAQ routes
            Route::get('faq', [SupportTicketController::class, 'faq'])->name('faq');
            Route::post('faq/{faq}/helpful', [SupportTicketController::class, 'markFaqHelpful'])->name('faq.helpful');
        });
    });
});

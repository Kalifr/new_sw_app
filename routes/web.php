<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProfileCompletionController;
use App\Http\Controllers\RfqController;
use App\Http\Controllers\RfqQuoteController;
use App\Http\Controllers\MessageController;
use App\Http\Middleware\EnsureProfileIsComplete;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

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

        // Product routes
        Route::resource('products', ProductController::class);
        Route::patch('/products/{product}/publish', [ProductController::class, 'publish'])->name('products.publish');

        // RFQ routes
        Route::get('/rfqs', [RfqController::class, 'index'])->name('rfqs.index');
        Route::get('/rfqs/create', [RfqController::class, 'create'])->name('rfqs.create');
        Route::post('/rfqs', [RfqController::class, 'store'])->name('rfqs.store');
        Route::get('/rfqs/{rfq}', [RfqController::class, 'show'])->name('rfqs.show');
        Route::get('/rfqs/{rfq}/edit', [RfqController::class, 'edit'])->name('rfqs.edit');
        Route::patch('/rfqs/{rfq}', [RfqController::class, 'update'])->name('rfqs.update');
        Route::delete('/rfqs/{rfq}', [RfqController::class, 'destroy'])->name('rfqs.destroy');
        
        // RFQ Quote Routes
        Route::get('/rfqs/{rfq}/quotes/create', [RfqQuoteController::class, 'create'])->name('rfqs.quotes.create');
        Route::post('/rfqs/{rfq}/quotes', [RfqQuoteController::class, 'store'])->name('rfqs.quotes.store');
        Route::get('/quotes/{quote}/edit', [RfqQuoteController::class, 'edit'])->name('quotes.edit');
        Route::patch('/quotes/{quote}', [RfqQuoteController::class, 'update'])->name('quotes.update');
        Route::delete('/quotes/{quote}', [RfqQuoteController::class, 'destroy'])->name('quotes.destroy');
        Route::patch('/quotes/{quote}/status', [RfqQuoteController::class, 'updateStatus'])->name('quotes.status.update');

        // Message Routes
        Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
        Route::get('/messages/threads/{thread}', [MessageController::class, 'show'])->name('messages.show');
        Route::post('/messages/threads', [MessageController::class, 'createThread'])->name('messages.threads.store');
        Route::post('/messages/threads/{thread}', [MessageController::class, 'store'])->name('messages.store');
        Route::post('/messages/email-reply', [MessageController::class, 'processEmailReply'])->name('messages.email-reply');
        Route::post('/messages/{message}/read', [MessageController::class, 'markAsRead'])->name('messages.mark-read');
    });
});

require __DIR__.'/auth.php';

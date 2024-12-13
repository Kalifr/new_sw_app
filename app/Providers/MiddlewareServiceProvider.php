<?php

namespace App\Providers;

use App\Http\Middleware\Role;
use App\Http\Middleware\EnsureProfileIsComplete;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\ServiceProvider;

class MiddlewareServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register middleware bindings
    }

    public function boot(Kernel $kernel): void
    {
        // Register middleware aliases
        $kernel->aliasMiddleware('role', Role::class);
        $kernel->aliasMiddleware('profile.complete', EnsureProfileIsComplete::class);
    }
} 
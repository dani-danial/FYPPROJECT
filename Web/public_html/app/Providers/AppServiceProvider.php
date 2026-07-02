<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL; // <--- This MUST be exactly this

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // This fixes the 404 errors by forcing ngrok to use HTTPS
        if (str_contains(config('app.url'), 'ngrok-free.dev')) {
            URL::forceScheme('https');
        }
    }
}
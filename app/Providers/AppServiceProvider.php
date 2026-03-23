<?php

namespace App\Providers;

use App\Models\ProductPrice;
use App\Observers\ProductPriceObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        ProductPrice::observe(ProductPriceObserver::class);

        RateLimiter::for('register', function (Request $request) {
            return Limit::perMinute(1)->by($request->ip())->response(function () {
                return back()->withErrors([
                    'email' => 'Demasiados intentos. Por favor esperá un minuto antes de intentarlo de nuevo.',
                ]);
            });
        });
    }
}

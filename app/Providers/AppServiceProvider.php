<?php

namespace App\Providers;

use App\Models\ProductPrice;
use App\Observers\ProductPriceObserver;
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
    }
}

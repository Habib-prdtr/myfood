<?php

namespace App\Providers;

use App\Models\Foods;
use Illuminate\Support\ServiceProvider;

class FoodsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(Foods::class, function ($app) {
            return new Foods();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}

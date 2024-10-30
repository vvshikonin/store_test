<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\TransactionService;
use App\Services\RetailCRMService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $link = config('app.crm_url');
        $token = config('app.crm_token');

        $this->app->singleton('retail-crm-service', function ($app) use($link, $token) {
            return new RetailCRMService($link, $token);
        });

        $this->app->singleton('transaction-service', function ($app) {
            return new TransactionService();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}

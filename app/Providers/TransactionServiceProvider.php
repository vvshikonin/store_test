<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Services\Transactions\Products\InvoiceProductTransactions;
use App\Services\Transactions\Products\ProductRefundTransactions;
use App\Services\Transactions\Products\ContractorRefundTransactions;

use App\Services\Transactions\Cash\InvoiceCashTransactions;

class TransactionServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(InvoiceProductTransactions::class, function ($app) {
            return new InvoiceProductTransactions();
        });

        $this->app->singleton(InvoiceCashTransactions::class, function ($app) {
            return new InvoiceCashTransactions();
        });

        $this->app->singleton(ProductRefundTransactions::class, function ($app) {
            return new ProductRefundTransactions();
        });

        $this->app->singleton(ContractorRefundTransactions::class, function ($app) {
            return new ContractorRefundTransactions();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}

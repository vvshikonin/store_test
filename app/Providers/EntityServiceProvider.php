<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Services\Entities\InvoiceService;
use App\Services\Entities\EmployeeService;
use App\Services\Entities\MoneyRefundService;
use App\Services\Entities\ProductRefunds\ProductRefundService;
use App\Services\Entities\DebtPaymentService;

use App\Services\Transactions\Products\InvoiceProductTransactions;
use App\Services\Transactions\Products\ProductRefundTransactions;

use App\Services\Transactions\Cash\InvoiceCashTransactions;
use App\Services\Transactions\Cash\MoneyRefundCashTransaction;

class EntityServiceProvider extends ServiceProvider
{
    /**
     * Регистрация сервисов.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(MoneyRefundService::class, function ($app) {
            return new MoneyRefundService(
                $app->make(MoneyRefundCashTransaction::class)
            );
        });

        $this->app->singleton(InvoiceService::class, function ($app) {
            return new InvoiceService(
                $app->make(InvoiceProductTransactions::class),
                $app->make(InvoiceCashTransactions::class),
                $app->make(MoneyRefundService::class),
                $app->make(DebtPaymentService::class),
            );
        });

        $this->app->singleton(ProductRefundService::class, function ($app) {
            return new ProductRefundService(
                $app->make(ProductRefundTransactions::class),
            );
        });

        $this->app->singleton(EmployeeService::class, function ($app) {
            return new EmployeeService();
        });

        $this->app->singleton(DebtPaymentService::class, function ($app) {
            return new DebtPaymentService();
        });
    }

    /**
     * Загрузка сервисов после регистрации.
     *
     * @return void
     */
    public function boot()
    {
        // Регистрируйте зависимости и настройки сервисов здесь
    }
}

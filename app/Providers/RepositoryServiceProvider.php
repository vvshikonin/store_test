<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
// use App\Repositories\InvoiceRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Регистрация сервисов.
     *
     * @return void
     */
    public function register()
    {
        // $this->app->bind(InvoiceRepository::class, function ($app) {
        //     return new InvoiceRepository();
        // });
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

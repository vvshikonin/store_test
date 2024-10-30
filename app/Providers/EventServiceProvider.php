<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use App\Events\DefectUpdated;
use App\Events\ProductUpdated;
use App\Events\ProductRefundUpdated;
use App\Events\InvoicePaymentStatusSet;
use App\Events\InvoiceProductRefused;
use App\Listeners\LogInvoiceHistory;
use App\Listeners\LogProductRefusedHistory;
use App\Listeners\UpdateProductSaleHistory;
use App\Listeners\SyncDefectToRetailCrmListener;
use App\Listeners\SyncProductRefundToRetailCrmListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        DefectUpdated::class => [
            SyncDefectToRetailCrmListener::class,
        ],
        ProductRefundUpdated::class => [
            SyncProductRefundToRetailCrmListener::class,
        ],
        ProductUpdated::class => [
            UpdateProductSaleHistory::class,
        ],
        InvoicePaymentStatusSet::class => [
            LogInvoiceHistory::class,
        ],
        InvoiceProductRefused::class => [
            LogProductRefusedHistory::class,
        ],
    ];
}

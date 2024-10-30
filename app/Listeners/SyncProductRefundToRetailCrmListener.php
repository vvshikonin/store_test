<?php

namespace App\Listeners;

use App\Events\ProductRefundUpdated;
use App\Services\Entities\ProductRefunds\ProductRefundToRetailOrderConverter;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SyncProductRefundToRetailCrmListener
{
    protected $converter;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(ProductRefundToRetailOrderConverter $converter)
    {
        $this->converter = $converter;
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\ProductRefundUpdated  $event
     * @return void
     */
    public function handle(ProductRefundUpdated $event)
    {
        $order = $this->converter->updateOrderFromProductRefund($event->productRefund);
        $this->converter->updateRetailOrder($order);
    }
}


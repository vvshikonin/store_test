<?php

namespace App\Listeners;

use App\Events\DefectUpdated;
use App\Services\Entities\Defects\DefectToRetailOrderConverter;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SyncDefectToRetailCrmListener
{
    protected $converter;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(DefectToRetailOrderConverter $converter)
    {
        $this->converter = $converter;
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\DefectUpdated  $event
     * @return void
     */
    public function handle(DefectUpdated $event)
    {
        $order = $this->converter->updateOrderFromDefect($event->defect);
        $this->converter->updateRetailOrder($order);
    }
}


<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\V1\ProductRefund;

class ProductRefundUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $productRefund;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(ProductRefund $productRefund)
    {
        $this->productRefund = $productRefund;
    }
}

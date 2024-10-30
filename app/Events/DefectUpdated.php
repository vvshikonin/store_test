<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\V1\Defect;

class DefectUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $defect;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Defect $defect)
    {
        $this->defect = $defect;
    }
}

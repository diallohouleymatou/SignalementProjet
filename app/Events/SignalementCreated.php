<?php

namespace App\Events;

use App\Modules\Signalement\Models\Signalement;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SignalementCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Signalement $signalement;

    /**
     * Create a new event instance.
     */
    public function __construct(Signalement $signalement)
    {
        $this->signalement = $signalement;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('signalements'),
        ];
    }
}

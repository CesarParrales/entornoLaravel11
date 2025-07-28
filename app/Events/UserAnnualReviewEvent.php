<?php

namespace App\Events;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserAnnualReviewEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public User $user;
    public Carbon $anniversaryEndDate;

    /**
     * Create a new event instance.
     */
    public function __construct(User $user, Carbon $anniversaryEndDate)
    {
        $this->user = $user;
        $this->anniversaryEndDate = $anniversaryEndDate;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'), // Opcional, si se necesita broadcast
        ];
    }
}

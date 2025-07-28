<?php

namespace App\Events;

use Carbon\Carbon;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MonthlyBonusReviewEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Carbon $monthToReview;

    /**
     * Create a new event instance.
     *
     * @param Carbon $monthToReview Any Carbon date within the month to be reviewed.
     *                              The listener will typically use $monthToReview->startOfMonth() and $monthToReview->endOfMonth().
     */
    public function __construct(Carbon $monthToReview)
    {
        $this->monthToReview = $monthToReview;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'), // Opcional
        ];
    }
}

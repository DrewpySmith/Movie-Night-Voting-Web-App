<?php

namespace App\Events;

use App\Models\MovieRoom;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RoomUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public MovieRoom $room,
        public string $action,
    ) {}

    public function broadcastOn(): Channel
    {
        return new Channel('room.' . $this->room->id);
    }

    public function broadcastWith(): array
    {
        return [
            'action' => $this->action,
            'status' => $this->room->status,
            'winner_movie_id' => $this->room->winner_movie_id,
        ];
    }
}

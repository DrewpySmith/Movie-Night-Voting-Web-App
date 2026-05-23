<?php

namespace App\Events;

use App\Models\Movie;
use App\Models\MovieRoom;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MovieSuggested implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public MovieRoom $room,
        public Movie $movie,
        public User $suggestedBy,
    ) {}

    public function broadcastOn(): Channel
    {
        return new Channel('room.' . $this->room->id);
    }

    public function broadcastWith(): array
    {
        return [
            'movie_id' => $this->movie->id,
            'title' => $this->movie->title,
            'suggested_by' => $this->suggestedBy->name,
        ];
    }
}

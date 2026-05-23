<?php

namespace App\Events;

use App\Models\Movie;
use App\Models\MovieRoom;
use App\Models\MovieVote;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VoteCast implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public MovieRoom $room,
        public Movie $movie,
        public MovieVote $vote,
    ) {}

    public function broadcastOn(): Channel
    {
        return new PrivateChannel('room.' . $this->room->id);
    }

    public function broadcastWith(): array
    {
        return [
            'movie_id' => $this->movie->id,
            'vote' => $this->vote->vote,
            'user_id' => $this->vote->user_id,
        ];
    }
}

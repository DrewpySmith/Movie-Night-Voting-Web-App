<?php

namespace App\Repositories;

use App\Models\MovieRoom;
use App\Models\MovieVote;
use Illuminate\Support\Facades\DB;

class VoteRepository
{
    public function getUserVote(MovieRoom $room, int $movieId, int $userId): ?MovieVote
    {
        return MovieVote::where([
            'room_id' => $room->id,
            'movie_id' => $movieId,
            'user_id' => $userId,
        ])->first();
    }

    public function getRoomVotes(MovieRoom $room)
    {
        return MovieVote::where('room_id', $room->id)
            ->select('movie_id', DB::raw("SUM(CASE WHEN vote = 'up' THEN 1 ELSE 0 END) as upvotes"),
                DB::raw("SUM(CASE WHEN vote = 'down' THEN 1 ELSE 0 END) as downvotes"),
                DB::raw("SUM(CASE WHEN vote = 'up' THEN 1 WHEN vote = 'down' THEN -1 ELSE 0 END) as score"))
            ->groupBy('movie_id')
            ->orderByDesc('score')
            ->get();
    }
}

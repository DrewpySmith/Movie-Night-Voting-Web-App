<?php

namespace App\Repositories;

use App\Models\Movie;
use App\Models\MovieRoom;
use Illuminate\Pagination\LengthAwarePaginator;

class MovieRepository
{
    public function findByOmdbId(string $omdbId): ?Movie
    {
        return Movie::where('omdb_id', $omdbId)->first();
    }

    public function getRoomMovies(MovieRoom $room)
    {
        return $room->movies()->withPivot('suggested_by', 'status')->with('votes')->get();
    }

    public function isInRoom(MovieRoom $room, Movie $movie): bool
    {
        return $room->movies()->where('movie_id', $movie->id)->exists();
    }

    public function suggestToRoom(MovieRoom $room, Movie $movie, int $userId): void
    {
        $room->movies()->attach($movie->id, [
            'suggested_by' => $userId,
            'status' => 'pending',
        ]);
    }

    public function removeFromRoom(MovieRoom $room, Movie $movie): void
    {
        $room->movies()->detach($movie->id);
    }
}

<?php

namespace App\Policies;

use App\Models\Movie;
use App\Models\MovieRoom;
use App\Models\User;

class RoomPolicy
{
    public function view(?User $user, MovieRoom $room): bool
    {
        if ($room->visibility === 'public') {
            return true;
        }
        return $user && $room->isMember($user);
    }

    public function create(?User $user): bool
    {
        return $user !== null;
    }

    public function update(User $user, MovieRoom $room): bool
    {
        return $room->isHost($user);
    }

    public function delete(User $user, MovieRoom $room): bool
    {
        return $room->isHost($user) || $user->is_admin;
    }

    public function remove(User $user, MovieRoom $room, Movie $movie): bool
    {
        return $room->isHost($user);
    }
}

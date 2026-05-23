<?php

namespace App\Repositories;

use App\Enums\RoomStatus;
use App\Models\MovieRoom;
use Illuminate\Pagination\LengthAwarePaginator;

class RoomRepository
{
    public function findById(int $id): ?MovieRoom
    {
        return MovieRoom::with(['host', 'members', 'movies', 'winner'])->find($id);
    }

    public function getPublicRooms(int $perPage = 20): LengthAwarePaginator
    {
        return MovieRoom::where('visibility', 'public')
            ->where('status', RoomStatus::Open->value)
            ->withCount('members', 'movies')
            ->with('host')
            ->latest()
            ->paginate($perPage);
    }

    public function getUserRooms(int $userId, int $perPage = 20): LengthAwarePaginator
    {
        return MovieRoom::whereHas('members', fn($q) => $q->where('user_id', $userId))
            ->withCount('members', 'movies')
            ->with('host')
            ->latest()
            ->paginate($perPage);
    }

    public function findByInviteCode(string $code): ?MovieRoom
    {
        return MovieRoom::where('invite_code', $code)
            ->where('status', RoomStatus::Open->value)
            ->first();
    }
}

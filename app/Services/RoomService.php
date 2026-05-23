<?php

namespace App\Services;

use App\Enums\NotificationType;
use App\Enums\RoomStatus;
use App\Enums\RoomVisibility;
use App\Models\MovieRoom;
use App\Models\User;
use App\Notifications\RoomNotification;
use Illuminate\Support\Str;

class RoomService
{
    public function createRoom(array $data, User $host): MovieRoom
    {
        $room = MovieRoom::create([
            'host_id' => $host->id,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'visibility' => $data['visibility'] ?? RoomVisibility::Public->value,
            'scheduled_at' => $data['scheduled_at'] ?? null,
            'status' => RoomStatus::Open->value,
        ]);

        $room->members()->attach($host->id, [
            'role' => 'host',
            'joined_at' => now(),
        ]);

        return $room;
    }

    public function joinRoom(MovieRoom $room, User $user): void
    {
        if ($room->isMember($user)) {
            return;
        }

        $room->members()->attach($user->id, [
            'role' => 'member',
            'joined_at' => now(),
        ]);

        if ($room->host_id !== $user->id) {
            $room->host->notify(new RoomNotification(
                NotificationType::NewMemberJoined,
                [
                    'message' => $user->name . ' joined ' . $room->title,
                    'room_id' => $room->id,
                    'actor_name' => $user->name,
                    'room_title' => $room->title,
                    'action_url' => '/rooms/' . $room->id,
                ]
            ));
        }
    }

    public function leaveRoom(MovieRoom $room, User $user): void
    {
        $room->members()->detach($user->id);
    }

    public function joinByCode(string $code, User $user): ?MovieRoom
    {
        $room = MovieRoom::where('invite_code', Str::upper($code))
            ->where('status', RoomStatus::Open->value)
            ->first();

        if (!$room) {
            return null;
        }

        $this->joinRoom($room, $user);

        return $room;
    }

    public function closeRoom(MovieRoom $room): void
    {
        $room->update(['status' => RoomStatus::Closed->value]);
    }

    public function regenerateInviteCode(MovieRoom $room): string
    {
        $code = Str::upper(Str::random(6));
        $room->update(['invite_code' => $code]);
        return $code;
    }

    public function transferHost(MovieRoom $room, User $newHost): void
    {
        $oldHost = $room->host;

        $room->update(['host_id' => $newHost->id]);

        $room->members()->updateExistingPivot($oldHost->id, ['role' => 'member']);
        $room->members()->updateExistingPivot($newHost->id, ['role' => 'host']);
    }
}

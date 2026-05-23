<?php

use App\Models\MovieRoom;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('room.{roomId}', function ($user, $roomId) {
    $room = MovieRoom::find($roomId);
    if (!$room) return false;
    return $room->isMember($user);
});

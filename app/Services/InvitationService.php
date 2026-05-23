<?php

namespace App\Services;

use App\Enums\InvitationStatus;
use App\Enums\NotificationType;
use App\Jobs\SendInviteNotification;
use App\Models\Invitation;
use App\Models\MovieRoom;
use App\Models\User;
use App\Notifications\RoomNotification;
use Illuminate\Support\Str;

class InvitationService
{
    public function createInvitation(MovieRoom $room, User $inviter, ?string $email = null): Invitation
    {
        $invitation = Invitation::create([
            'room_id' => $room->id,
            'inviter_id' => $inviter->id,
            'invitee_email' => $email,
            'token' => Str::random(32),
            'status' => InvitationStatus::Pending->value,
            'expires_at' => now()->addDays(7),
        ]);

        if ($email) {
            SendInviteNotification::dispatch($invitation);
        }

        $inviter->notify(new RoomNotification(
            NotificationType::InvitationCreated,
            [
                'message' => 'You invited ' . ($email ?? 'someone') . ' to ' . $room->title,
                'room_id' => $room->id,
                'room_title' => $room->title,
                'invitee_email' => $email ?? 'unknown',
                'action_url' => '/rooms/' . $room->id,
            ]
        ));

        return $invitation;
    }

    public function acceptInvitation(string $token, User $user): ?MovieRoom
    {
        $invitation = Invitation::where('token', $token)
            ->where('status', InvitationStatus::Pending->value)
            ->first();

        if (!$invitation || $invitation->isExpired()) {
            return null;
        }

        $invitation->update([
            'status' => InvitationStatus::Accepted->value,
            'invitee_id' => $user->id,
        ]);

        $room = $invitation->room;
        $room->members()->syncWithoutDetaching([$user->id => ['role' => 'member', 'joined_at' => now()]]);

        $invitation->inviter->notify(new RoomNotification(
            NotificationType::InvitationAccepted,
            [
                'message' => $user->name . ' accepted your invitation to ' . $room->title,
                'room_id' => $room->id,
                'actor_name' => $user->name,
                'room_title' => $room->title,
                'action_url' => '/rooms/' . $room->id,
            ]
        ));

        return $room;
    }

    public function declineInvitation(string $token): void
    {
        Invitation::where('token', $token)
            ->where('status', InvitationStatus::Pending->value)
            ->update(['status' => InvitationStatus::Declined->value]);
    }

    public function getPendingInvitations(User $user)
    {
        return Invitation::where('invitee_email', $user->email)
            ->where('status', InvitationStatus::Pending->value)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->with('room', 'inviter')
            ->get();
    }
}

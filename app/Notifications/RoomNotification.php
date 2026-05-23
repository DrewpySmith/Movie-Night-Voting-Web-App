<?php

namespace App\Notifications;

use App\Enums\NotificationType;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RoomNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public NotificationType $type,
        public array $data,
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database', 'broadcast'];
    }

    public function toMail($notifiable): MailMessage
    {
        [$emoji, $subject] = match ($this->type) {
            NotificationType::VoteReceived => ['🗳️', 'New vote on your room'],
            NotificationType::NewMemberJoined => ['👋', 'Someone joined your room'],
            NotificationType::InvitationCreated => ['📧', 'Invitation sent'],
            NotificationType::InvitationAccepted => ['✅', 'Invitation accepted'],
            NotificationType::WinnerDeclared => ['🏆', 'Winner declared!'],
        };

        return (new MailMessage)
            ->subject("$emoji $subject - " . config('app.name'))
            ->markdown('mail.notification', [
                'emoji' => $emoji,
                'subject' => $subject,
                'type' => $this->type->value,
                'message' => $this->data['message'] ?? '',
                'actor_name' => $this->data['actor_name'] ?? null,
                'movie_title' => $this->data['movie_title'] ?? null,
                'room_title' => $this->data['room_title'] ?? null,
                'action_url' => $this->data['action_url'] ?? '/dashboard',
                'invitee_email' => $this->data['invitee_email'] ?? null,
                'vote_type' => $this->data['vote_type'] ?? null,
            ]);
    }

    public function toDatabase($notifiable): array
    {
        return array_merge($this->data, ['type' => $this->type->value]);
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'type' => $this->type->value,
            'data' => $this->data,
        ]);
    }
}

<?php

namespace Tests\Feature;

use App\Enums\NotificationType;
use App\Enums\VoteType;
use App\Models\Movie;
use App\Models\MovieRoom;
use App\Models\MovieVote;
use App\Models\User;
use App\Notifications\RoomNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_host_receives_notification_when_someone_votes()
    {
        Notification::fake();

        $host = User::factory()->create(['email_verified_at' => now()]);
        $voter = User::factory()->create(['email_verified_at' => now()]);
        $room = MovieRoom::factory()->create(['host_id' => $host->id]);
        $movie = Movie::factory()->create();
        $room->members()->attach([$host->id, $voter->id]);
        $room->movies()->attach($movie->id, ['suggested_by' => $voter->id]);

        $this->actingAs($voter)
            ->postJson("/api/v1/rooms/{$room->id}/movies/{$movie->id}/vote", [
                'vote' => 'up',
            ]);

        Notification::assertSentTo(
            $host,
            RoomNotification::class,
            function ($notification) use ($voter, $movie) {
                return $notification->type === NotificationType::VoteReceived
                    && str_contains($notification->data['message'], $voter->name)
                    && str_contains($notification->data['message'], $movie->title);
            }
        );
    }

    public function test_host_does_not_receive_notification_when_voting_self()
    {
        Notification::fake();

        $host = User::factory()->create(['email_verified_at' => now()]);
        $room = MovieRoom::factory()->create(['host_id' => $host->id]);
        $movie = Movie::factory()->create();
        $room->members()->attach($host->id);
        $room->movies()->attach($movie->id, ['suggested_by' => $host->id]);

        $this->actingAs($host)
            ->postJson("/api/v1/rooms/{$room->id}/movies/{$movie->id}/vote", [
                'vote' => 'up',
            ]);

        Notification::assertNothingSent();
    }

    public function test_host_receives_notification_when_someone_joins()
    {
        Notification::fake();

        $host = User::factory()->create(['email_verified_at' => now()]);
        $joiner = User::factory()->create(['email_verified_at' => now()]);
        $room = MovieRoom::factory()->create(['host_id' => $host->id]);
        $room->members()->attach($host->id);

        $this->actingAs($joiner)
            ->postJson("/api/v1/rooms/{$room->id}/join");

        Notification::assertSentTo(
            $host,
            RoomNotification::class,
            function ($notification) use ($joiner, $room) {
                return $notification->type === NotificationType::NewMemberJoined
                    && str_contains($notification->data['message'], $joiner->name)
                    && str_contains($notification->data['message'], $room->title);
            }
        );
    }

    public function test_inviter_receives_notification_when_invitation_accepted()
    {
        Notification::fake();

        $inviter = User::factory()->create(['email_verified_at' => now()]);
        $invitee = User::factory()->create(['email_verified_at' => now()]);
        $room = MovieRoom::factory()->create(['host_id' => $inviter->id]);
        $room->members()->attach($inviter->id);

        $invitation = \App\Models\Invitation::factory()->create([
            'room_id' => $room->id,
            'inviter_id' => $inviter->id,
            'invitee_email' => $invitee->email,
            'status' => 'pending',
            'expires_at' => now()->addDays(7),
        ]);

        $this->actingAs($invitee)
            ->get(route('invitations.accept', $invitation->token));

        Notification::assertSentTo(
            $inviter,
            RoomNotification::class,
            function ($notification) use ($invitee, $room) {
                return $notification->type === NotificationType::InvitationAccepted
                    && str_contains($notification->data['message'], $invitee->name)
                    && str_contains($notification->data['message'], $room->title);
            }
        );
    }

    public function test_members_receive_notification_when_winner_declared()
    {
        Notification::fake();

        $host = User::factory()->create(['email_verified_at' => now()]);
        $member = User::factory()->create(['email_verified_at' => now()]);
        $room = MovieRoom::factory()->create(['host_id' => $host->id]);
        $movie = Movie::factory()->create();
        $room->members()->attach([$host->id, $member->id]);
        $room->movies()->attach($movie->id, ['suggested_by' => $host->id]);

        MovieVote::factory()->create([
            'room_id' => $room->id,
            'movie_id' => $movie->id,
            'user_id' => $member->id,
            'vote' => 'up',
        ]);

        $this->actingAs($host)
            ->postJson("/api/v1/rooms/{$room->id}/declare-winner/{$movie->id}");

        Notification::assertSentTo(
            $member,
            RoomNotification::class,
            function ($notification) use ($movie) {
                return $notification->type === NotificationType::WinnerDeclared
                    && str_contains($notification->data['message'], $movie->title);
            }
        );

        Notification::assertNotSentTo($host, RoomNotification::class);
    }

    public function test_unread_notifications_are_returned_via_livewire()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $user->notify(new RoomNotification(
            NotificationType::VoteReceived,
            ['message' => 'Test notification', 'room_id' => 1, 'action_url' => '/rooms/1']
        ));

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertSeeLivewire('notification-bell');
    }

    public function test_notification_includes_mail_channel()
    {
        $notification = new RoomNotification(
            NotificationType::VoteReceived,
            ['message' => 'Test', 'room_id' => 1, 'actor_name' => 'Test', 'action_url' => '/rooms/1']
        );

        $channels = $notification->via(new User);
        $this->assertContains('mail', $channels);
    }

    public function test_notification_mail_has_correct_subject()
    {
        $notification = new RoomNotification(
            NotificationType::VoteReceived,
            [
                'message' => 'Alice upvoted The Matrix',
                'room_id' => 1,
                'actor_name' => 'Alice',
                'action_url' => '/rooms/1',
            ]
        );

        $mail = $notification->toMail(new User);
        $this->assertStringContainsString('New vote', $mail->subject);
        $this->assertStringContainsString('Alice', $mail->viewData['actor_name'] ?? '');
    }

    public function test_notification_mail_rendered_output_contains_message()
    {
        $notification = new RoomNotification(
            NotificationType::VoteReceived,
            [
                'message' => 'Alice upvoted The Matrix',
                'room_id' => 1,
                'actor_name' => 'Alice',
                'movie_title' => 'The Matrix',
                'action_url' => '/rooms/1',
            ]
        );

        $mail = $notification->toMail(new User);
        $rendered = $mail->render();
        $this->assertStringContainsString('Alice upvoted The Matrix', $rendered);
        $this->assertStringContainsString('The Matrix', $rendered);
        $this->assertStringContainsString('View Room', $rendered);
    }
}

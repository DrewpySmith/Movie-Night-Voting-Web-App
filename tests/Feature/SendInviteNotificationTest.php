<?php

namespace Tests\Feature;

use App\Enums\InvitationStatus;
use App\Jobs\SendInviteNotification;
use App\Mail\InvitationMail;
use App\Models\Invitation;
use App\Models\MovieRoom;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SendInviteNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_sends_email(): void
    {
        Mail::fake();

        $host = User::factory()->create(['name' => 'TestHost']);
        $room = MovieRoom::factory()->create(['title' => 'TestRoom']);
        $invitation = Invitation::factory()->create([
            'room_id' => $room->id,
            'inviter_id' => $host->id,
            'invitee_email' => 'guest@example.com',
            'token' => 'test-email-token',
            'status' => InvitationStatus::Pending->value,
        ]);

        (new SendInviteNotification($invitation))->handle();

        Mail::assertSent(InvitationMail::class);
    }

    public function test_job_skips_when_no_email(): void
    {
        Mail::fake();

        $invitation = Invitation::factory()->create([
            'invitee_email' => null,
        ]);

        (new SendInviteNotification($invitation))->handle();

        Mail::assertNothingSent();
    }

    public function test_mailable_contains_correct_data(): void
    {
        $host = User::factory()->create(['name' => 'MovieHost']);
        $room = MovieRoom::factory()->create(['title' => 'Friday Night Movies']);
        $invitation = Invitation::factory()->create([
            'room_id' => $room->id,
            'inviter_id' => $host->id,
            'invitee_email' => 'guest@example.com',
            'token' => 'verify-token-abc',
        ]);

        $mailable = new InvitationMail($invitation);

        $mailable->assertHasSubject('MovieHost invited you to Friday Night Movies');
        $mailable->assertSeeInHtml('MovieHost');
        $mailable->assertSeeInHtml('Friday Night Movies');
        $mailable->assertSeeInHtml('/invitations/verify-token-abc/accept');
    }
}

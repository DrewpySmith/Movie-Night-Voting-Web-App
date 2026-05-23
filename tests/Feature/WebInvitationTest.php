<?php

namespace Tests\Feature;

use App\Enums\InvitationStatus;
use App\Models\Invitation;
use App\Models\MovieRoom;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebInvitationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_redirected_to_login_when_accepting(): void
    {
        $response = $this->get('/invitations/some-token/accept');
        $response->assertRedirect(route('login'));
    }

    public function test_user_can_accept_invitation_via_web(): void
    {
        $host = User::factory()->create();
        $user = User::factory()->create();
        $room = MovieRoom::factory()->create(['host_id' => $host->id]);
        $room->members()->attach($host->id, ['role' => 'host', 'joined_at' => now()]);

        Invitation::factory()->create([
            'room_id' => $room->id,
            'inviter_id' => $host->id,
            'invitee_email' => $user->email,
            'token' => 'web-accept-token',
            'status' => InvitationStatus::Pending->value,
        ]);

        $response = $this->actingAs($user)->get('/invitations/web-accept-token/accept');

        $response->assertRedirect(route('rooms.show', $room));
        $response->assertSessionHas('status');
        $this->assertDatabaseHas('invitations', [
            'token' => 'web-accept-token',
            'status' => InvitationStatus::Accepted->value,
        ]);
    }

    public function test_user_redirected_on_expired_invitation(): void
    {
        $host = User::factory()->create();
        $user = User::factory()->create();
        $room = MovieRoom::factory()->create(['host_id' => $host->id]);

        Invitation::factory()->expired()->create([
            'room_id' => $room->id,
            'inviter_id' => $host->id,
            'invitee_email' => $user->email,
            'token' => 'web-expired-token',
        ]);

        $response = $this->actingAs($user)->get('/invitations/web-expired-token/accept');

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHasErrors('invitation');
    }

    public function test_user_can_decline_invitation_via_web(): void
    {
        $host = User::factory()->create();
        $user = User::factory()->create();
        $room = MovieRoom::factory()->create(['host_id' => $host->id]);

        Invitation::factory()->create([
            'room_id' => $room->id,
            'inviter_id' => $host->id,
            'invitee_email' => $user->email,
            'token' => 'web-decline-token',
            'status' => InvitationStatus::Pending->value,
        ]);

        $response = $this->actingAs($user)->post('/invitations/web-decline-token/decline');

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('status');
        $this->assertDatabaseHas('invitations', [
            'token' => 'web-decline-token',
            'status' => InvitationStatus::Declined->value,
        ]);
    }
}

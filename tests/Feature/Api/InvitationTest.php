<?php

namespace Tests\Feature\Api;

use App\Enums\InvitationStatus;
use App\Models\Invitation;
use App\Models\MovieRoom;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvitationTest extends TestCase
{
    use RefreshDatabase;

    public function test_host_can_invite_by_email(): void
    {
        $host = User::factory()->create();
        $room = MovieRoom::factory()->create(['host_id' => $host->id]);
        $room->members()->attach($host->id, ['role' => 'host', 'joined_at' => now()]);

        $response = $this->actingAs($host)->postJson("/api/v1/rooms/{$room->id}/invite", [
            'email' => 'guest@example.com',
        ]);

        $response->assertOk();
        $response->assertJsonStructure(['message', 'token']);
        $this->assertDatabaseHas('invitations', [
            'room_id' => $room->id,
            'invitee_email' => 'guest@example.com',
            'status' => InvitationStatus::Pending->value,
        ]);
    }

    public function test_non_host_cannot_invite(): void
    {
        $host = User::factory()->create();
        $user = User::factory()->create();
        $room = MovieRoom::factory()->create(['host_id' => $host->id]);
        $room->members()->attach($user->id, ['role' => 'member', 'joined_at' => now()]);

        $response = $this->actingAs($user)->postJson("/api/v1/rooms/{$room->id}/invite", [
            'email' => 'guest@example.com',
        ]);

        $response->assertForbidden();
    }

    public function test_user_can_accept_invitation(): void
    {
        $host = User::factory()->create();
        $user = User::factory()->create();
        $room = MovieRoom::factory()->create(['host_id' => $host->id]);
        $room->members()->attach($host->id, ['role' => 'host', 'joined_at' => now()]);

        $invitation = Invitation::factory()->create([
            'room_id' => $room->id,
            'inviter_id' => $host->id,
            'invitee_email' => $user->email,
            'token' => 'test-token-123',
            'status' => InvitationStatus::Pending->value,
        ]);

        $response = $this->actingAs($user)->postJson("/api/v1/invitations/test-token-123/accept");

        $response->assertOk();
        $response->assertJson(['message' => 'Invitation accepted']);
        $this->assertDatabaseHas('invitations', [
            'id' => $invitation->id,
            'status' => InvitationStatus::Accepted->value,
            'invitee_id' => $user->id,
        ]);
        $this->assertDatabaseHas('room_members', [
            'room_id' => $room->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_user_cannot_accept_expired_invitation(): void
    {
        $host = User::factory()->create();
        $user = User::factory()->create();
        $room = MovieRoom::factory()->create(['host_id' => $host->id]);

        Invitation::factory()->expired()->create([
            'room_id' => $room->id,
            'inviter_id' => $host->id,
            'invitee_email' => $user->email,
            'token' => 'expired-token',
        ]);

        $response = $this->actingAs($user)->postJson("/api/v1/invitations/expired-token/accept");

        $response->assertNotFound();
        $response->assertJson(['message' => 'Invalid or expired invitation']);
    }

    public function test_user_can_decline_invitation(): void
    {
        $host = User::factory()->create();
        $user = User::factory()->create();
        $room = MovieRoom::factory()->create(['host_id' => $host->id]);

        Invitation::factory()->create([
            'room_id' => $room->id,
            'inviter_id' => $host->id,
            'invitee_email' => $user->email,
            'token' => 'decline-token',
            'status' => InvitationStatus::Pending->value,
        ]);

        $response = $this->actingAs($user)->postJson("/api/v1/invitations/decline-token/decline");

        $response->assertOk();
        $response->assertJson(['message' => 'Invitation declined']);
        $this->assertDatabaseHas('invitations', [
            'token' => 'decline-token',
            'status' => InvitationStatus::Declined->value,
        ]);
    }

    public function test_user_can_list_pending_invitations(): void
    {
        $host = User::factory()->create();
        $user = User::factory()->create();

        $rooms = MovieRoom::factory()->count(3)->create(['host_id' => $host->id]);

        foreach ($rooms as $room) {
            Invitation::factory()->create([
                'room_id' => $room->id,
                'inviter_id' => $host->id,
                'invitee_email' => $user->email,
            ]);
        }

        $response = $this->actingAs($user)->getJson("/api/v1/invitations");

        $response->assertOk();
        $response->assertJsonCount(3, 'data');
    }
}

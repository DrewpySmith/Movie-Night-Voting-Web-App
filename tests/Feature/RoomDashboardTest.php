<?php

namespace Tests\Feature;

use App\Models\MovieRoom;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_created_room_appears_on_dashboard()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user)
            ->post(route('rooms.store'), [
                'title' => 'My Test Room',
                'description' => 'A test room',
                'visibility' => 'public',
            ])
            ->assertRedirect();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertSee('My Test Room');
    }

    public function test_dashboard_shows_my_rooms_section()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user)
            ->post(route('rooms.store'), [
                'title' => 'My Private Room',
                'visibility' => 'private',
            ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertSee('My Private Room');
        $response->assertSee('private');
    }

    public function test_public_room_appears_in_public_section()
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($admin)
            ->post(route('rooms.store'), [
                'title' => 'Public Party Room',
                'visibility' => 'public',
            ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertSee('Public Party Room');
    }

    public function test_host_can_transfer_ownership()
    {
        $host = User::factory()->create(['email_verified_at' => now()]);
        $member = User::factory()->create(['email_verified_at' => now()]);
        $room = MovieRoom::factory()->create(['host_id' => $host->id]);

        $room->members()->attach([$host->id, $member->id]);

        $this->actingAs($host)
            ->post(route('rooms.transfer', $room), ['user_id' => $member->id])
            ->assertSessionHas('status');

        $this->assertEquals($member->id, $room->fresh()->host_id);
    }

    public function test_non_host_cannot_transfer_ownership()
    {
        $host = User::factory()->create(['email_verified_at' => now()]);
        $member = User::factory()->create(['email_verified_at' => now()]);
        $other = User::factory()->create(['email_verified_at' => now()]);
        $room = MovieRoom::factory()->create(['host_id' => $host->id]);

        $room->members()->attach([$host->id, $member->id, $other->id]);

        $this->actingAs($other)
            ->post(route('rooms.transfer', $room), ['user_id' => $member->id])
            ->assertForbidden();

        $this->assertEquals($host->id, $room->fresh()->host_id);
    }

    public function test_cannot_transfer_to_non_member()
    {
        $host = User::factory()->create(['email_verified_at' => now()]);
        $nonMember = User::factory()->create(['email_verified_at' => now()]);
        $room = MovieRoom::factory()->create(['host_id' => $host->id]);

        $room->members()->attach($host->id);

        $this->actingAs($host)
            ->post(route('rooms.transfer', $room), ['user_id' => $nonMember->id])
            ->assertSessionHasErrors('transfer');

        $this->assertEquals($host->id, $room->fresh()->host_id);
    }
}

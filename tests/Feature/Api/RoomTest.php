<?php

namespace Tests\Feature\Api;

use App\Models\MovieRoom;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_create_room(): void
    {
        $response = $this->postJson('/api/v1/rooms', [
            'title' => 'Test Room',
        ]);

        $response->assertUnauthorized();
    }

    public function test_user_can_create_room(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/rooms', [
            'title' => 'Friday Movie Night',
            'description' => 'Watching action movies',
            'visibility' => 'public',
        ]);

        $response->assertCreated();
        $response->assertJsonFragment(['title' => 'Friday Movie Night']);
    }

    public function test_user_can_join_room(): void
    {
        $host = User::factory()->create();
        $member = User::factory()->create();

        $room = MovieRoom::factory()->create([
            'host_id' => $host->id,
        ]);

        $room->members()->attach($host->id, ['role' => 'host', 'joined_at' => now()]);

        $response = $this->actingAs($member)->postJson("/api/v1/rooms/{$room->id}/join");

        $response->assertOk();
        $this->assertTrue($room->fresh()->isMember($member));
    }

    public function test_user_cannot_join_same_room_twice(): void
    {
        $host = User::factory()->create();
        $member = User::factory()->create();

        $room = MovieRoom::factory()->create(['host_id' => $host->id]);
        $room->members()->attach([$host->id => ['role' => 'host', 'joined_at' => now()], $member->id => ['role' => 'member', 'joined_at' => now()]]);

        $response = $this->actingAs($member)->postJson("/api/v1/rooms/{$room->id}/join");

        $response->assertStatus(409);
    }

    public function test_only_host_can_update_room(): void
    {
        $host = User::factory()->create();
        $member = User::factory()->create();

        $room = MovieRoom::factory()->create(['host_id' => $host->id]);
        $room->members()->attach([$host->id => ['role' => 'host', 'joined_at' => now()], $member->id => ['role' => 'member', 'joined_at' => now()]]);

        $response = $this->actingAs($member)->putJson("/api/v1/rooms/{$room->id}", [
            'title' => 'Hacked Title',
        ]);

        $response->assertForbidden();
    }

    public function test_host_can_update_room(): void
    {
        $host = User::factory()->create();

        $room = MovieRoom::factory()->create(['host_id' => $host->id]);
        $room->members()->attach($host->id, ['role' => 'host', 'joined_at' => now()]);

        $response = $this->actingAs($host)->putJson("/api/v1/rooms/{$room->id}", [
            'title' => 'Updated Title',
        ]);

        $response->assertOk();
        $this->assertEquals('Updated Title', $room->fresh()->title);
    }
}

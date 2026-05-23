<?php

namespace Tests\Feature\Api;

use App\Models\Movie;
use App\Models\MovieRoom;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VoteTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_vote(): void
    {
        $room = MovieRoom::factory()->create();
        $movie = Movie::factory()->create();

        $response = $this->postJson("/api/v1/rooms/{$room->id}/movies/{$movie->id}/vote", [
            'vote' => 'up',
        ]);

        $response->assertUnauthorized();
    }

    public function test_user_can_cast_vote(): void
    {
        $user = User::factory()->create();
        $room = MovieRoom::factory()->create(['host_id' => $user->id]);
        $room->members()->attach($user->id, ['role' => 'host', 'joined_at' => now()]);
        $movie = Movie::factory()->create();
        $room->movies()->attach($movie->id, ['suggested_by' => $user->id, 'status' => 'pending']);

        $response = $this->actingAs($user)->postJson("/api/v1/rooms/{$room->id}/movies/{$movie->id}/vote", [
            'vote' => 'up',
        ]);

        $response->assertCreated();
        $response->assertJsonFragment(['vote' => 'up']);
    }

    public function test_user_can_change_vote(): void
    {
        $user = User::factory()->create();
        $room = MovieRoom::factory()->create(['host_id' => $user->id]);
        $room->members()->attach($user->id, ['role' => 'host', 'joined_at' => now()]);
        $movie = Movie::factory()->create();
        $room->movies()->attach($movie->id, ['suggested_by' => $user->id, 'status' => 'pending']);

        $this->actingAs($user)->postJson("/api/v1/rooms/{$room->id}/movies/{$movie->id}/vote", ['vote' => 'up']);
        $response = $this->actingAs($user)->postJson("/api/v1/rooms/{$room->id}/movies/{$movie->id}/vote", ['vote' => 'down']);

        $response->assertOk();
        $response->assertJsonFragment(['vote' => 'down']);
    }

    public function test_invalid_vote_type_is_rejected(): void
    {
        $user = User::factory()->create();
        $room = MovieRoom::factory()->create(['host_id' => $user->id]);
        $room->members()->attach($user->id, ['role' => 'host', 'joined_at' => now()]);
        $movie = Movie::factory()->create();
        $room->movies()->attach($movie->id, ['suggested_by' => $user->id, 'status' => 'pending']);

        $response = $this->actingAs($user)->postJson("/api/v1/rooms/{$room->id}/movies/{$movie->id}/vote", [
            'vote' => 'invalid',
        ]);

        $response->assertStatus(422);
    }

    public function test_user_can_remove_vote(): void
    {
        $user = User::factory()->create();
        $room = MovieRoom::factory()->create(['host_id' => $user->id]);
        $room->members()->attach($user->id, ['role' => 'host', 'joined_at' => now()]);
        $movie = Movie::factory()->create();
        $room->movies()->attach($movie->id, ['suggested_by' => $user->id, 'status' => 'pending']);

        $this->actingAs($user)->postJson("/api/v1/rooms/{$room->id}/movies/{$movie->id}/vote", ['vote' => 'up']);
        $response = $this->actingAs($user)->deleteJson("/api/v1/rooms/{$room->id}/movies/{$movie->id}/vote");

        $response->assertOk();
        $this->assertDatabaseMissing('movie_votes', [
            'room_id' => $room->id,
            'movie_id' => $movie->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_vote_tally_returns_scores(): void
    {
        $host = User::factory()->create();
        $voter1 = User::factory()->create();
        $voter2 = User::factory()->create();
        $room = MovieRoom::factory()->create(['host_id' => $host->id]);
        $room->members()->attach([$host->id => ['role' => 'host', 'joined_at' => now()], $voter1->id => ['role' => 'member', 'joined_at' => now()], $voter2->id => ['role' => 'member', 'joined_at' => now()]]);
        $movie = Movie::factory()->create();
        $room->movies()->attach($movie->id, ['suggested_by' => $host->id, 'status' => 'pending']);

        $this->actingAs($voter1)->postJson("/api/v1/rooms/{$room->id}/movies/{$movie->id}/vote", ['vote' => 'up']);
        $this->actingAs($voter2)->postJson("/api/v1/rooms/{$room->id}/movies/{$movie->id}/vote", ['vote' => 'up']);

        $response = $this->actingAs($host)->getJson("/api/v1/rooms/{$room->id}/votes");

        $response->assertOk();
        $response->assertJsonStructure(['tally']);
    }

    public function test_winner_returns_winner_when_exists(): void
    {
        $host = User::factory()->create();
        $voter = User::factory()->create();
        $room = MovieRoom::factory()->create(['host_id' => $host->id]);
        $room->members()->attach([$host->id => ['role' => 'host', 'joined_at' => now()], $voter->id => ['role' => 'member', 'joined_at' => now()]]);
        $movie = Movie::factory()->create();
        $room->movies()->attach($movie->id, ['suggested_by' => $host->id, 'status' => 'pending']);

        $this->actingAs($voter)->postJson("/api/v1/rooms/{$room->id}/movies/{$movie->id}/vote", ['vote' => 'up']);

        $response = $this->actingAs($host)->getJson("/api/v1/rooms/{$room->id}/winner");

        $response->assertOk();
        $response->assertJsonFragment(['title' => $movie->title]);
    }

    public function test_winner_returns_404_when_no_votes(): void
    {
        $host = User::factory()->create();
        $room = MovieRoom::factory()->create(['host_id' => $host->id]);
        $room->members()->attach($host->id, ['role' => 'host', 'joined_at' => now()]);

        $response = $this->actingAs($host)->getJson("/api/v1/rooms/{$room->id}/winner");

        $response->assertStatus(404);
    }

    public function test_host_can_declare_winner(): void
    {
        $host = User::factory()->create();
        $member = User::factory()->create();
        $room = MovieRoom::factory()->create(['host_id' => $host->id]);
        $room->members()->attach([$host->id => ['role' => 'host', 'joined_at' => now()], $member->id => ['role' => 'member', 'joined_at' => now()]]);
        $movie = Movie::factory()->create();
        $room->movies()->attach($movie->id, ['suggested_by' => $host->id, 'status' => 'pending']);

        $response = $this->actingAs($host)->postJson("/api/v1/rooms/{$room->id}/declare-winner/{$movie->id}");

        $response->assertOk();
        $this->assertEquals($movie->id, $room->fresh()->winner_movie_id);
    }

    public function test_non_host_cannot_declare_winner(): void
    {
        $host = User::factory()->create();
        $member = User::factory()->create();
        $room = MovieRoom::factory()->create(['host_id' => $host->id]);
        $room->members()->attach([$host->id => ['role' => 'host', 'joined_at' => now()], $member->id => ['role' => 'member', 'joined_at' => now()]]);
        $movie = Movie::factory()->create();
        $room->movies()->attach($movie->id, ['suggested_by' => $host->id, 'status' => 'pending']);

        $response = $this->actingAs($member)->postJson("/api/v1/rooms/{$room->id}/declare-winner/{$movie->id}");

        $response->assertForbidden();
    }
}

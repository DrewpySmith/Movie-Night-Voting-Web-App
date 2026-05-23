<?php

namespace Tests\Feature\Api;

use App\Models\Movie;
use App\Models\MovieRoom;
use App\Models\User;
use App\Services\OmdbService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MovieTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_search(): void
    {
        $response = $this->getJson('/api/v1/movies/search?q=batman');

        $response->assertUnauthorized();
    }

    public function test_search_returns_movies_from_omdb(): void
    {
        $this->mock(OmdbService::class, function ($mock) {
            $mock->shouldReceive('search')
                ->once()
                ->with('batman', 1)
                ->andReturn([
                    'movies' => [
                        ['imdbID' => 'tt0096895', 'Title' => 'Batman', 'Year' => '1989', 'Poster' => 'https://example.com/poster.jpg'],
                        ['imdbID' => 'tt0468569', 'Title' => 'The Dark Knight', 'Year' => '2008', 'Poster' => 'https://example.com/dk.jpg'],
                    ],
                    'total' => 2,
                ]);
        });

        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/v1/movies/search?q=batman');

        $response->assertOk();
        $response->assertJsonCount(2, 'movies');
        $response->assertJsonFragment(['title' => 'Batman']);
    }

    public function test_search_validates_query(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/v1/movies/search');

        $response->assertStatus(422);
    }

    public function test_fetch_returns_movie_from_omdb(): void
    {
        $this->mock(OmdbService::class, function ($mock) {
            $mock->shouldReceive('findById')
                ->once()
                ->with('tt0096895')
                ->andReturn(Movie::factory()->make(['omdb_id' => 'tt0096895', 'title' => 'Batman']));
        });

        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/v1/movies/fetch/tt0096895');

        $response->assertOk();
        $response->assertJsonFragment(['title' => 'Batman']);
    }

    public function test_fetch_returns_404_when_not_found(): void
    {
        $this->mock(OmdbService::class, function ($mock) {
            $mock->shouldReceive('findById')
                ->once()
                ->with('tt0000000')
                ->andReturn(null);
        });

        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/v1/movies/fetch/tt0000000');

        $response->assertStatus(404);
    }

    public function test_show_returns_movie_details(): void
    {
        $user = User::factory()->create();
        $movie = Movie::factory()->create();

        $response = $this->actingAs($user)->getJson("/api/v1/movies/{$movie->id}");

        $response->assertOk();
        $response->assertJsonFragment(['title' => $movie->title]);
    }

    public function test_user_can_suggest_movie_to_room(): void
    {
        $user = User::factory()->create();
        $room = MovieRoom::factory()->create(['host_id' => $user->id]);
        $room->members()->attach($user->id, ['role' => 'host', 'joined_at' => now()]);
        $movie = Movie::factory()->create();

        $response = $this->actingAs($user)->postJson("/api/v1/rooms/{$room->id}/movies", [
            'omdb_id' => $movie->omdb_id,
        ]);

        $response->assertOk();
        $this->assertTrue($room->fresh()->hasMovie($movie));
    }

    public function test_cannot_suggest_duplicate_movie_to_room(): void
    {
        $user = User::factory()->create();
        $room = MovieRoom::factory()->create(['host_id' => $user->id]);
        $room->members()->attach($user->id, ['role' => 'host', 'joined_at' => now()]);
        $movie = Movie::factory()->create();
        $room->movies()->attach($movie->id, ['suggested_by' => $user->id, 'status' => 'pending']);

        $response = $this->actingAs($user)->postJson("/api/v1/rooms/{$room->id}/movies", [
            'omdb_id' => $movie->omdb_id,
        ]);

        $response->assertStatus(409);
    }

    public function test_host_can_remove_movie_from_room(): void
    {
        $user = User::factory()->create();
        $room = MovieRoom::factory()->create(['host_id' => $user->id]);
        $room->members()->attach($user->id, ['role' => 'host', 'joined_at' => now()]);
        $movie = Movie::factory()->create();
        $room->movies()->attach($movie->id, ['suggested_by' => $user->id, 'status' => 'pending']);

        $response = $this->actingAs($user)->deleteJson("/api/v1/rooms/{$room->id}/movies/{$movie->id}");

        $response->assertOk();
        $this->assertFalse($room->fresh()->hasMovie($movie));
    }

    public function test_non_host_cannot_remove_movie(): void
    {
        $host = User::factory()->create();
        $member = User::factory()->create();
        $room = MovieRoom::factory()->create(['host_id' => $host->id]);
        $room->members()->attach([$host->id => ['role' => 'host', 'joined_at' => now()], $member->id => ['role' => 'member', 'joined_at' => now()]]);
        $movie = Movie::factory()->create();
        $room->movies()->attach($movie->id, ['suggested_by' => $host->id, 'status' => 'pending']);

        $response = $this->actingAs($member)->deleteJson("/api/v1/rooms/{$room->id}/movies/{$movie->id}");

        $response->assertForbidden();
    }
}

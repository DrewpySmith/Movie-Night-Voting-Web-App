<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Services\TmdbService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrendingTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_access_trending(): void
    {
        $response = $this->getJson('/api/v1/trending');

        $response->assertUnauthorized();
    }

    public function test_trending_returns_movies(): void
    {
        $this->mock(TmdbService::class, function ($mock) {
            $mock->shouldReceive('trending')
                ->once()
                ->with('week', 1)
                ->andReturn([
                    'movies' => [
                        ['id' => 1, 'tmdb_id' => 1, 'title' => 'Trending 1', 'poster_url' => null, 'year' => '2025', 'rating' => 8.0, 'overview' => 'Desc', 'genre_ids' => [], 'source' => 'tmdb'],
                        ['id' => 2, 'tmdb_id' => 2, 'title' => 'Trending 2', 'poster_url' => null, 'year' => '2025', 'rating' => 7.5, 'overview' => 'Desc', 'genre_ids' => [], 'source' => 'tmdb'],
                    ],
                    'source' => 'tmdb',
                ]);
        });

        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/v1/trending');

        $response->assertOk();
        $response->assertJsonCount(2, 'movies');
        $response->assertJsonFragment(['title' => 'Trending 1']);
        $response->assertJsonFragment(['source' => 'tmdb']);
    }

    public function test_trending_accepts_time_window(): void
    {
        $this->mock(TmdbService::class, function ($mock) {
            $mock->shouldReceive('trending')
                ->once()
                ->with('day', 1)
                ->andReturn(['movies' => [], 'source' => 'tmdb']);
        });

        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/v1/trending?time_window=day');

        $response->assertOk();
    }

    public function test_trending_returns_empty_when_no_movies(): void
    {
        $this->mock(TmdbService::class, function ($mock) {
            $mock->shouldReceive('trending')
                ->once()
                ->andReturn(['movies' => [], 'source' => 'local']);
        });

        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/v1/trending');

        $response->assertOk();
        $response->assertJsonCount(0, 'movies');
    }
}

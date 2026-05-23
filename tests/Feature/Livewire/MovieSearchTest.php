<?php

namespace Tests\Feature\Livewire;

use App\Livewire\MovieSearch;
use App\Models\Movie;
use App\Models\MovieRoom;
use App\Models\User;
use App\Services\TmdbService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MovieSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_search_movies(): void
    {
        $this->mock(TmdbService::class, function ($mock) {
            $mock->shouldReceive('search')
                ->once()
                ->with('batman')
                ->andReturn([
                    'movies' => [
                        ['tmdb_id' => 12345, 'title' => 'Batman', 'year' => '1989', 'poster_url' => 'https://example.com/poster.jpg', 'overview' => null],
                    ],
                    'total' => 1,
                ]);
        });

        $user = User::factory()->create();
        $room = MovieRoom::factory()->create(['host_id' => $user->id]);

        Livewire::actingAs($user)
            ->test(MovieSearch::class, ['room' => $room])
            ->set('query', 'batman')
            ->assertSet('showResults', true)
            ->assertCount('results', 1);
    }

    public function test_short_query_does_not_search(): void
    {
        $user = User::factory()->create();
        $room = MovieRoom::factory()->create(['host_id' => $user->id]);

        Livewire::actingAs($user)
            ->test(MovieSearch::class, ['room' => $room])
            ->set('query', 'b')
            ->assertSet('showResults', false)
            ->assertCount('results', 0);
    }

    public function test_can_suggest_movie_to_room(): void
    {
        $user = User::factory()->create();
        $room = MovieRoom::factory()->create(['host_id' => $user->id]);
        $room->members()->attach($user->id, ['role' => 'host', 'joined_at' => now()]);

        $tmdbId = 27205;
        $movie = Movie::factory()->create(['tmdb_id' => $tmdbId, 'omdb_id' => 'tmdb_' . $tmdbId]);

        $this->mock(TmdbService::class, function ($mock) use ($movie, $tmdbId) {
            $mock->shouldReceive('findMovie')
                ->once()
                ->with($tmdbId)
                ->andReturn($movie);
        });

        Livewire::actingAs($user)
            ->test(MovieSearch::class, ['room' => $room])
            ->call('suggest', $tmdbId)
            ->assertDispatched('movie-suggested');

        $this->assertTrue($room->fresh()->hasMovie($movie));
    }

    public function test_cannot_suggest_duplicate_movie(): void
    {
        $user = User::factory()->create();
        $room = MovieRoom::factory()->create(['host_id' => $user->id]);
        $room->members()->attach($user->id, ['role' => 'host', 'joined_at' => now()]);

        $tmdbId = 27205;
        $movie = Movie::factory()->create(['tmdb_id' => $tmdbId, 'omdb_id' => 'tmdb_' . $tmdbId]);
        $room->movies()->attach($movie->id, ['suggested_by' => $user->id, 'status' => 'pending']);

        $this->mock(TmdbService::class, function ($mock) use ($movie, $tmdbId) {
            $mock->shouldReceive('findMovie')
                ->once()
                ->with($tmdbId)
                ->andReturn($movie);
        });

        Livewire::actingAs($user)
            ->test(MovieSearch::class, ['room' => $room])
            ->call('suggest', $tmdbId)
            ->assertDispatched('notify');
    }
}

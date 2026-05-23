<?php

namespace Tests\Feature\Livewire;

use App\Livewire\TrendingMovies;
use App\Models\User;
use App\Services\TmdbService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TrendingMoviesTest extends TestCase
{
    use RefreshDatabase;

    public function test_displays_trending_movies(): void
    {
        $this->mock(TmdbService::class, function ($mock) {
            $mock->shouldReceive('trending')
                ->once()
                ->andReturn([
                    'movies' => [
                        ['id' => 1, 'tmdb_id' => 1, 'title' => 'Movie 1', 'poster_url' => null, 'year' => '2025', 'rating' => 8.0, 'overview' => '', 'genre_ids' => [], 'source' => 'tmdb'],
                        ['id' => 2, 'tmdb_id' => 2, 'title' => 'Movie 2', 'poster_url' => null, 'year' => '2025', 'rating' => 7.0, 'overview' => '', 'genre_ids' => [], 'source' => 'tmdb'],
                    ],
                    'source' => 'tmdb',
                ]);
        });

        Livewire::test(TrendingMovies::class)
            ->assertSet('loading', false)
            ->assertCount('movies', 2)
            ->assertSee('Movie 1')
            ->assertSee('Movie 2');
    }

    public function test_shows_empty_state(): void
    {
        $this->mock(TmdbService::class, function ($mock) {
            $mock->shouldReceive('trending')
                ->once()
                ->andReturn(['movies' => [], 'source' => 'local']);
        });

        Livewire::test(TrendingMovies::class)
            ->assertSet('loading', false)
            ->assertCount('movies', 0);
    }

    public function test_sets_source_correctly(): void
    {
        $this->mock(TmdbService::class, function ($mock) {
            $mock->shouldReceive('trending')
                ->once()
                ->andReturn(['movies' => [], 'source' => 'local']);
        });

        Livewire::test(TrendingMovies::class)
            ->assertSet('source', 'local');
    }
}

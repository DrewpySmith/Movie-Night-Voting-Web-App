<?php

namespace Tests\Unit;

use App\Models\Movie;
use App\Services\TmdbService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TmdbServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_local_movies_when_no_api_key(): void
    {
        config(['services.tmdb.api_key' => null]);

        Movie::factory()->create(['title' => 'Movie A', 'imdb_rating' => 8.5]);
        Movie::factory()->create(['title' => 'Movie B', 'imdb_rating' => 7.0]);

        $service = app(TmdbService::class);
        $result = $service->trending();

        $this->assertEquals('local', $result['source']);
        $this->assertCount(2, $result['movies']);
        $this->assertEquals('Movie A', $result['movies'][0]['title']);
    }

    public function test_fetches_from_tmdb_when_api_key_present(): void
    {
        config(['services.tmdb.api_key' => 'test-key']);

        Http::fake([
            'api.themoviedb.org/*' => Http::response([
                'results' => [
                    [
                        'id' => 123,
                        'title' => 'Trending Movie',
                        'poster_path' => '/poster.jpg',
                        'release_date' => '2025-06-01',
                        'vote_average' => 8.2,
                        'overview' => 'A great movie',
                        'genre_ids' => [28, 12],
                    ],
                ],
            ]),
        ]);

        $service = app(TmdbService::class);
        $result = $service->trending();

        $this->assertEquals('tmdb', $result['source']);
        $this->assertCount(1, $result['movies']);
        $this->assertEquals('Trending Movie', $result['movies'][0]['title']);
        $this->assertEquals(123, $result['movies'][0]['tmdb_id']);
    }

    public function test_falls_back_to_local_when_tmdb_fails(): void
    {
        config(['services.tmdb.api_key' => 'test-key']);

        Http::fake([
            'api.themoviedb.org/*' => Http::response([], 500),
        ]);

        Movie::factory()->create(['title' => 'Fallback Movie', 'imdb_rating' => 9.0]);

        $service = app(TmdbService::class);
        $result = $service->trending();

        $this->assertEquals('local', $result['source']);
        $this->assertCount(1, $result['movies']);
        $this->assertEquals('Fallback Movie', $result['movies'][0]['title']);
    }

    public function test_caches_results(): void
    {
        config(['services.tmdb.api_key' => 'test-key']);

        Http::fake([
            'api.themoviedb.org/*' => Http::response([
                'results' => [
                    ['id' => 1, 'title' => 'Cached Movie', 'poster_path' => null, 'release_date' => '2025-01-01', 'vote_average' => 7.0, 'overview' => '', 'genre_ids' => []],
                ],
            ]),
        ]);

        $service = app(TmdbService::class);
        $result = $service->trending();

        $this->assertCount(1, $result['movies']);

        Http::assertSentCount(1);

        $result = $service->trending();

        Http::assertSentCount(1);
    }

    public function test_search_returns_results(): void
    {
        config(['services.tmdb.api_key' => 'test-key']);

        Http::fake([
            'api.themoviedb.org/3/search/movie*' => Http::response([
                'results' => [
                    [
                        'id' => 123,
                        'title' => 'Batman Begins',
                        'poster_path' => '/batman.jpg',
                        'release_date' => '2005-06-15',
                        'vote_average' => 8.2,
                        'overview' => 'A great movie',
                    ],
                ],
                'total_results' => 1,
            ]),
        ]);

        $service = app(TmdbService::class);
        $result = $service->search('batman');

        $this->assertCount(1, $result['movies']);
        $this->assertEquals('Batman Begins', $result['movies'][0]['title']);
        $this->assertEquals(123, $result['movies'][0]['tmdb_id']);
    }

    public function test_search_returns_empty_when_no_api_key(): void
    {
        config(['services.tmdb.api_key' => null]);

        $service = app(TmdbService::class);
        $result = $service->search('batman');

        $this->assertEmpty($result['movies']);
    }

    public function test_find_movie_creates_record(): void
    {
        config(['services.tmdb.api_key' => 'test-key']);

        Http::fake([
            'api.themoviedb.org/3/movie/27205*' => Http::response([
                'id' => 27205,
                'title' => 'Inception',
                'release_date' => '2010-07-16',
                'overview' => 'A thief who steals corporate secrets through dream-sharing technology.',
                'poster_path' => '/inception.jpg',
                'runtime' => 148,
                'vote_average' => 8.369,
                'genres' => [
                    ['id' => 28, 'name' => 'Action'],
                    ['id' => 878, 'name' => 'Science Fiction'],
                ],
            ]),
        ]);

        $service = app(TmdbService::class);
        $movie = $service->findMovie(27205);

        $this->assertNotNull($movie);
        $this->assertEquals(27205, $movie->tmdb_id);
        $this->assertEquals('tmdb_27205', $movie->omdb_id);
        $this->assertEquals('Inception', $movie->title);
        $this->assertEquals('2010', $movie->year);
        $this->assertEquals('Action, Science Fiction', $movie->genre);
        $this->assertStringContainsString('inception.jpg', $movie->poster_url);
    }

    public function test_find_movie_returns_null_when_no_api_key(): void
    {
        config(['services.tmdb.api_key' => null]);

        $service = app(TmdbService::class);
        $movie = $service->findMovie(27205);

        $this->assertNull($movie);
    }

    public function test_returns_empty_when_no_data(): void
    {
        config(['services.tmdb.api_key' => null]);

        $service = app(TmdbService::class);
        $result = $service->trending();

        $this->assertEquals('local', $result['source']);
        $this->assertEmpty($result['movies']);
    }
}

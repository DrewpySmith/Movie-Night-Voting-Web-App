<?php

namespace App\Services;

use App\Models\Movie;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TmdbService
{
    protected ?string $apiKey;
    protected int $ttl;
    protected string $imageBase;

    public function __construct()
    {
        $this->apiKey = config('services.tmdb.api_key');
        $this->ttl = config('services.tmdb.cache_ttl', 21600);
        $this->imageBase = 'https://image.tmdb.org/t/p/w500';
    }

    public function search(string $query, int $page = 1): array
    {
        $cacheKey = 'tmdb.search.' . md5($query) . ".page.{$page}";

        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        if (!$this->apiKey) {
            return ['movies' => [], 'total' => 0];
        }

        try {
            $response = Http::timeout(10)
                ->withOptions($this->httpOptions())
                ->retry(3, 500)
                ->get('https://api.themoviedb.org/3/search/movie', [
                    'api_key' => $this->apiKey,
                    'query' => $query,
                    'page' => $page,
                    'language' => 'en-US',
                ]);

            if ($response->failed()) {
                Log::warning('TMDB search failed', ['status' => $response->status()]);
                return ['movies' => [], 'total' => 0];
            }

            $body = $response->json();
            $results = $body['results'] ?? [];

            $result = [
                'movies' => array_map(fn ($m) => [
                    'tmdb_id' => $m['id'],
                    'title' => $m['title'],
                    'year' => isset($m['release_date']) ? substr($m['release_date'], 0, 4) : null,
                    'poster_url' => $m['poster_path']
                        ? $this->imageBase . $m['poster_path']
                        : null,
                    'overview' => $m['overview'] ?? null,
                ], $results),
                'total' => $body['total_results'] ?? 0,
            ];

            if (!empty($result['movies'])) {
                Cache::put($cacheKey, $result, $this->ttl);
            }

            return $result;
        } catch (\Exception $e) {
            Log::warning('TMDB search exception', ['message' => $e->getMessage()]);
            return ['movies' => [], 'total' => 0];
        }
    }

    public function findMovie(int $tmdbId): ?Movie
    {
        $cacheKey = "tmdb.movie.{$tmdbId}";

        $data = Cache::get($cacheKey);
        if ($data === null) {
            if (!$this->apiKey) {
                return null;
            }

            try {
                $response = Http::timeout(10)
                    ->withOptions($this->httpOptions())
                    ->retry(3, 500)
                    ->get("https://api.themoviedb.org/3/movie/{$tmdbId}", [
                        'api_key' => $this->apiKey,
                        'language' => 'en-US',
                    ]);

                if ($response->failed()) {
                    return null;
                }

                $data = $response->json();
                Cache::put($cacheKey, $data, $this->ttl);
            } catch (\Exception $e) {
                Log::warning('TMDB movie fetch exception', ['message' => $e->getMessage()]);
                return null;
            }
        }

        if (!$data) {
            return null;
        }

        return Movie::updateOrCreate(
            ['tmdb_id' => $tmdbId],
            [
                'omdb_id' => 'tmdb_' . $tmdbId,
                'title' => $data['title'] ?? 'Unknown',
                'year' => isset($data['release_date']) ? substr($data['release_date'], 0, 4) : null,
                'genre' => isset($data['genres']) ? implode(', ', array_column($data['genres'], 'name')) : null,
                'plot' => $data['overview'] ?? null,
                'poster_url' => isset($data['poster_path']) ? $this->imageBase . $data['poster_path'] : null,
                'runtime' => isset($data['runtime']) ? $data['runtime'] . ' min' : null,
                'imdb_rating' => $data['vote_average'] ?? null,
                'cached_at' => now(),
            ]
        );
    }

    public function trending(string $timeWindow = 'week', int $page = 1): array
    {
        $cacheKey = "tmdb.trending.{$timeWindow}.page.{$page}";

        return Cache::remember($cacheKey, $this->ttl, function () use ($timeWindow, $page) {
            if ($this->apiKey) {
                return $this->fetchFromTmdb($timeWindow, $page);
            }

            return $this->fetchFromLocal();
        });
    }

    protected function httpOptions(): array
    {
        $caPath = ini_get('curl.cainfo') ?: ini_get('openssl.cafile');

        if ($caPath) {
            return ['verify' => $caPath];
        }

        $xamppCa = 'C:\xampp\apache\bin\curl-ca-bundle.crt';
        if (file_exists($xamppCa)) {
            return ['verify' => $xamppCa];
        }

        return [];
    }

    protected function fetchFromTmdb(string $timeWindow, int $page): array
    {
        try {
            $response = Http::timeout(10)
                ->withOptions($this->httpOptions())
                ->retry(3, 500)
                ->get("https://api.themoviedb.org/3/trending/movie/{$timeWindow}", [
                    'api_key' => $this->apiKey,
                    'page' => $page,
                    'language' => 'en-US',
                ]);

            if ($response->failed()) {
                Log::warning('TMDB API request failed', ['status' => $response->status()]);
                return $this->fetchFromLocal();
            }

            $body = $response->json();
            $results = $body['results'] ?? [];

            return [
                'movies' => array_map(fn ($m) => $this->normalizeTmdbMovie($m), $results),
                'source' => 'tmdb',
            ];
        } catch (\Exception $e) {
            Log::warning('TMDB API exception', ['message' => $e->getMessage()]);
            return $this->fetchFromLocal();
        }
    }

    protected function fetchFromLocal(): array
    {
        try {
            $movies = Movie::withCount('votes')
                ->orderByDesc('votes_count')
                ->orderByDesc('imdb_rating')
                ->limit(20)
                ->get();
        } catch (QueryException) {
            return ['movies' => [], 'source' => 'local'];
        }

        return [
            'movies' => $movies->map(fn ($m) => [
                'id' => $m->id,
                'tmdb_id' => null,
                'title' => $m->title,
                'poster_url' => $m->poster_url,
                'year' => $m->year,
                'rating' => $m->imdb_rating,
                'overview' => $m->plot,
                'genre' => $m->genre,
                'source' => 'local',
            ])->toArray(),
            'source' => 'local',
        ];
    }

    protected function normalizeTmdbMovie(array $movie): array
    {
        return [
            'id' => $movie['id'],
            'tmdb_id' => $movie['id'],
            'title' => $movie['title'],
            'poster_url' => $movie['poster_path']
                ? $this->imageBase . $movie['poster_path']
                : null,
            'year' => isset($movie['release_date'])
                ? substr($movie['release_date'], 0, 4)
                : null,
            'rating' => $movie['vote_average'] ?? null,
            'overview' => $movie['overview'] ?? null,
            'genre_ids' => $movie['genre_ids'] ?? [],
            'source' => 'tmdb',
        ];
    }
}

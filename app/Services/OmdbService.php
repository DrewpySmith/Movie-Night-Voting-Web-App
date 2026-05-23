<?php

namespace App\Services;

use App\Exceptions\OmdbApiException;
use App\Models\Movie;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;

class OmdbService
{
    protected string $apiKey;
    protected int $ttl;
    protected int $maxRetries;

    public function __construct()
    {
        $this->apiKey = config('services.omdb.api_key');
        $this->ttl = config('services.omdb.cache_ttl', 3600);
        $this->maxRetries = config('services.omdb.max_retries', 5);
    }

    public function search(string $query, int $page = 1): array
    {
        $cacheKey = "omdb.search." . md5($query) . ".page.{$page}";

        return $this->fetchWithFallback($cacheKey, function () use ($query, $page) {
            $response = Http::baseUrl('https://www.omdbapi.com/')
                ->timeout(15)
                ->retry($this->maxRetries, 500, function ($ex) {
                    return $ex instanceof ConnectionException;
                })
                ->withOptions($this->httpOptions())
                ->get('/', [
                    'apikey' => $this->apiKey,
                    's' => $query,
                    'page' => $page,
                    'type' => 'movie',
                ]);

            if ($response->failed()) {
                throw new OmdbApiException('OMDb search failed: ' . $response->body());
            }

            $body = $response->json();

            if ($body['Response'] === 'False') {
                return ['movies' => [], 'total' => 0, 'error' => $body['Error'] ?? 'No results'];
            }

            return [
                'movies' => $body['Search'] ?? [],
                'total' => (int) ($body['totalResults'] ?? 0),
            ];
        });
    }

    public function findById(string $imdbId): ?Movie
    {
        $cacheKey = "omdb.id.{$imdbId}";

        $data = $this->fetchWithFallback($cacheKey, function () use ($imdbId) {
            $response = Http::baseUrl('https://www.omdbapi.com/')
                ->timeout(15)
                ->retry($this->maxRetries, 500, function ($ex) {
                    return $ex instanceof ConnectionException;
                })
                ->withOptions($this->httpOptions())
                ->get('/', [
                    'apikey' => $this->apiKey,
                    'i' => $imdbId,
                    'plot' => 'full',
                ]);

            if ($response->failed()) {
                throw new OmdbApiException('OMDb fetch failed: ' . $response->body());
            }

            $body = $response->json();

            if (($body['Response'] ?? 'True') === 'False') {
                return null;
            }

            return $body;
        });

        if (!$data || !is_array($data)) {
            return null;
        }

        return Movie::updateOrCreate(
            ['omdb_id' => $imdbId],
            [
                'title' => $data['Title'] ?? 'Unknown',
                'year' => $data['Year'] ?? null,
                'genre' => $data['Genre'] ?? null,
                'plot' => $data['Plot'] ?? null,
                'poster_url' => $data['Poster'] ?? null,
                'runtime' => $data['Runtime'] ?? null,
                'imdb_rating' => $data['imdbRating'] ?? null,
                'actors' => $data['Actors'] ?? null,
                'director' => $data['Director'] ?? null,
                'cached_at' => now(),
            ]
        );
    }

    public function findByTitle(string $title): ?Movie
    {
        $cacheKey = "omdb.title." . md5($title);

        $data = $this->fetchWithFallback($cacheKey, function () use ($title) {
            $response = Http::baseUrl('https://www.omdbapi.com/')
                ->timeout(15)
                ->retry($this->maxRetries, 500, function ($ex) {
                    return $ex instanceof ConnectionException;
                })
                ->withOptions($this->httpOptions())
                ->get('/', [
                    'apikey' => $this->apiKey,
                    't' => $title,
                    'plot' => 'full',
                ]);

            if ($response->failed()) {
                throw new OmdbApiException('OMDb fetch failed: ' . $response->body());
            }

            $body = $response->json();

            if (($body['Response'] ?? 'True') === 'False') {
                return null;
            }

            return $body;
        });

        if (!$data || !is_array($data)) {
            return null;
        }

        return Movie::updateOrCreate(
            ['omdb_id' => $data['imdbID']],
            [
                'title' => $data['Title'] ?? 'Unknown',
                'year' => $data['Year'] ?? null,
                'genre' => $data['Genre'] ?? null,
                'plot' => $data['Plot'] ?? null,
                'poster_url' => $data['Poster'] ?? null,
                'runtime' => $data['Runtime'] ?? null,
                'imdb_rating' => $data['imdbRating'] ?? null,
                'actors' => $data['Actors'] ?? null,
                'director' => $data['Director'] ?? null,
                'cached_at' => now(),
            ]
        );
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

    protected function fetchWithFallback(string $cacheKey, \Closure $apiCall): mixed
    {
        if ($cached = Cache::get($cacheKey)) {
            return $cached;
        }

        try {
            $result = $apiCall();
            Cache::put($cacheKey, $result, $this->ttl);
            return $result;
        } catch (ConnectionException $e) {
            $stale = Cache::get($cacheKey);
            if ($stale !== null) {
                return $stale;
            }
            throw new OmdbApiException('Connection to OMDb failed. Check your network and try again.', 503);
        }
    }
}

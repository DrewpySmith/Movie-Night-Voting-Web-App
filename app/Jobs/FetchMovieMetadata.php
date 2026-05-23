<?php

namespace App\Jobs;

use App\Models\Movie;
use App\Services\OmdbService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class FetchMovieMetadata implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Movie $movie
    ) {}

    public function handle(OmdbService $omdb): void
    {
        $fresh = $omdb->findById($this->movie->omdb_id);

        if ($fresh) {
            $this->movie->update([
                'title' => $fresh->title,
                'year' => $fresh->year,
                'genre' => $fresh->genre,
                'plot' => $fresh->plot,
                'poster_url' => $fresh->poster_url,
                'runtime' => $fresh->runtime,
                'imdb_rating' => $fresh->imdb_rating,
                'actors' => $fresh->actors,
                'director' => $fresh->director,
                'cached_at' => now(),
            ]);
        }
    }
}

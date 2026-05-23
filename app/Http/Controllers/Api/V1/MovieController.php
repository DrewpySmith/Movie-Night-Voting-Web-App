<?php

namespace App\Http\Controllers\Api\V1;

use App\Events\MovieSuggested;
use App\Http\Controllers\Controller;
use App\Http\Requests\SuggestMovieRequest;
use App\Http\Resources\MovieResource;
use App\Models\Movie;
use App\Models\MovieRoom;
use App\Repositories\MovieRepository;
use App\Services\OmdbService;
use App\Services\TmdbService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MovieController extends Controller
{
    public function __construct(
        protected OmdbService $omdbService,
        protected MovieRepository $movieRepository,
        protected TmdbService $tmdbService,
    ) {}

    public function search(Request $request): JsonResponse
    {
        $request->validate(['q' => 'required|string|min:2|max:255']);

        $results = $this->omdbService->search($request->q, $request->integer('page', 1));

        if (isset($results['error'])) {
            return response()->json($results, 404);
        }

        $movies = collect($results['movies'])->map(function ($item) {
            return Movie::updateOrCreate(
                ['omdb_id' => $item['imdbID']],
                [
                    'title' => $item['Title'] ?? 'Unknown',
                    'year' => $item['Year'] ?? null,
                    'poster_url' => $item['Poster'] ?? null,
                ]
            );
        });

        return response()->json([
            'movies' => MovieResource::collection($movies),
            'total' => $results['total'],
        ]);
    }

    public function suggest(SuggestMovieRequest $request, MovieRoom $room): JsonResponse
    {
        if (!$room->isMember($request->user())) {
            abort(403);
        }

        $movie = Movie::where('omdb_id', $request->omdb_id)->firstOrFail();

        if ($this->movieRepository->isInRoom($room, $movie)) {
            return response()->json(['message' => 'Movie already suggested'], 409);
        }

        $this->movieRepository->suggestToRoom($room, $movie, $request->user()->id);

        broadcast(new MovieSuggested($room, $movie, $request->user()))->toOthers();

        return response()->json([
            'message' => 'Movie suggested',
            'movie' => MovieResource::make($movie),
        ]);
    }

    public function remove(MovieRoom $room, Movie $movie): JsonResponse
    {
        $this->authorize('remove', [$room, $movie]);

        $this->movieRepository->removeFromRoom($room, $movie);

        return response()->json(['message' => 'Movie removed']);
    }

    public function show(Movie $movie): MovieResource
    {
        return MovieResource::make($movie->load('votes'));
    }

    public function fetch(string $omdbId): JsonResponse
    {
        $movie = $this->omdbService->findById($omdbId);

        if (!$movie) {
            return response()->json(['message' => 'Movie not found'], 404);
        }

        return response()->json([
            'movie' => MovieResource::make($movie),
        ]);
    }

    public function trending(Request $request): JsonResponse
    {
        $timeWindow = $request->enum('time_window', \App\Enums\TrendingTimeWindow::class) ?? \App\Enums\TrendingTimeWindow::Week;
        $page = $request->integer('page', 1);

        $results = $this->tmdbService->trending($timeWindow->value, $page);

        return response()->json($results);
    }
}

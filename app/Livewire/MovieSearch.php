<?php

namespace App\Livewire;

use App\Models\MovieRoom;
use App\Services\TmdbService;
use Livewire\Component;

class MovieSearch extends Component
{
    public MovieRoom $room;
    public string $query = '';
    public array $results = [];
    public bool $showResults = false;
    public bool $loading = false;
    public ?string $error = null;

    protected TmdbService $tmdbService;

    public function boot(TmdbService $tmdbService): void
    {
        $this->tmdbService = $tmdbService;
    }

    public function updatedQuery(): void
    {
        if (strlen($this->query) < 2) {
            $this->results = [];
            $this->showResults = false;
            return;
        }

        $this->loading = true;
        $this->error = null;

        try {
            $searchResults = $this->tmdbService->search($this->query);
            $this->results = $searchResults['movies'] ?? [];
            $this->showResults = true;
        } catch (\Exception $e) {
            $this->results = [];
            $this->showResults = true;
            $this->error = $e->getMessage() ?: 'Search failed. Try again.';
        } finally {
            $this->loading = false;
        }
    }

    public function suggest(int $tmdbId): void
    {
        try {
            $movie = $this->tmdbService->findMovie($tmdbId);
        } catch (\Exception $e) {
            $this->dispatch('notify', message: 'Could not load movie details. Try again.', type: 'error');
            return;
        }

        if (!$movie) {
            $this->dispatch('notify', message: 'Movie not found on TMDB', type: 'error');
            return;
        }

        if ($this->room->hasMovie($movie)) {
            $this->dispatch('notify', message: 'Movie already suggested', type: 'warning');
            return;
        }

        $this->room->movies()->attach($movie->id, [
            'suggested_by' => auth()->id(),
            'status' => 'pending',
        ]);

        $this->query = '';
        $this->results = [];
        $this->showResults = false;

        $this->dispatch('movie-suggested');
        $this->dispatch('notify', message: 'Movie suggested!', type: 'success');
    }

    public function render()
    {
        return view('livewire.movie-search');
    }
}

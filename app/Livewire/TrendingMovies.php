<?php

namespace App\Livewire;

use App\Services\TmdbService;
use Livewire\Component;

class TrendingMovies extends Component
{
    public array $movies = [];
    public string $source = '';
    public bool $loading = true;

    public function mount(TmdbService $tmdb): void
    {
        $result = $tmdb->trending();

        $this->movies = $result['movies'];
        $this->source = $result['source'];
        $this->loading = false;
    }

    public function render()
    {
        return view('livewire.trending-movies');
    }
}

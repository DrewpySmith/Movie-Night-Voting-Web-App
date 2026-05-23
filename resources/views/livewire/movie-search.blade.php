<div>
    <label class="text-lg font-black text-white" for="movie-search">Search Movies</label>
    <div class="mt-4 flex items-center gap-3 rounded-2xl border border-white/10 bg-surface-elevated px-4 py-3 transition-within">
        <svg class="h-5 w-5 shrink-0 text-zinc-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
        <input id="movie-search"
               type="text"
               wire:model.live.debounce.300ms="query"
               placeholder="Try Spider-Man or Grand Budapest..."
               class="w-full bg-transparent text-base text-white outline-none placeholder:text-zinc-500">
        @if($loading)
            <div class="animate-spin h-5 w-5 border-2 border-red-500 border-t-transparent rounded-full shrink-0"></div>
        @endif
    </div>

    @if($error)
        <p class="mt-3 text-sm text-red-400">{{ $error }}</p>
    @endif

    @if($showResults && strlen($query) >= 2)
        <div class="mt-4 grid gap-3 sm:grid-cols-2">
            @if(count($results) > 0)
                @foreach($results as $movie)
                    @if($movie['tmdb_id'])
                    <button wire:click="suggest({{ $movie['tmdb_id'] }})"
                            class="flex items-center gap-3 rounded-2xl bg-white/5 p-3 text-left transition hover:bg-white/10">
                        <img src="{{ $movie['poster_url'] ?? 'https://via.placeholder.com/44x66?text=N/A' }}" alt="" class="h-16 w-11 rounded-lg object-cover">
                        <span>
                            <span class="block font-bold text-white text-sm">{{ $movie['title'] }}</span>
                            <span class="text-xs text-zinc-500">{{ $movie['year'] }}</span>
                        </span>
                    </button>
                    @endif
                @endforeach
            @else
                <p class="text-sm text-zinc-400 sm:col-span-2">No new matches. Try another title.</p>
            @endif
        </div>
    @endif
</div>

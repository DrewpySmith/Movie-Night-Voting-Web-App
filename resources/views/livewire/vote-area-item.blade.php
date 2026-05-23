<article class="group relative overflow-hidden rounded-[1.5rem] bg-surface p-4 transition duration-300 hover:-translate-y-1">
    <div class="flex gap-4">
        <div class="h-44 w-28 shrink-0 rounded-2xl overflow-hidden shadow-2xl shadow-black/30 bg-surface-elevated">
            <img src="{{ $movie->poster_url ?? 'https://via.placeholder.com/112x176?text=No+Poster' }}" alt="{{ $movie->title }} poster" class="h-full w-full object-cover" loading="lazy">
        </div>
        <div class="flex min-w-0 flex-1 flex-col">
            <div>
                <div class="flex items-start justify-between gap-3">
                    <h3 class="line-clamp-2 text-lg font-black leading-tight text-white">{{ $movie->title }}</h3>
                </div>
                <p class="mt-1 text-sm text-zinc-400">{{ $movie->year }} &middot; {{ $movie->runtime }}</p>
                @if($movie->imdb_rating)
                    <p class="mt-1 text-sm font-bold text-yellow-300">★ {{ $movie->imdb_rating }}</p>
                @endif
            </div>
            @if($movie->plot)
                <p class="mt-4 line-clamp-2 text-sm leading-6 text-zinc-400">{{ Str::limit($movie->plot, 120) }}</p>
            @endif
            <div class="mt-auto flex items-center justify-between gap-3 pt-5">
                <div class="flex gap-2">
                    <button wire:click="vote({{ $movie->id }}, 'up')"
                            aria-label="Upvote"
                            class="grid h-11 w-12 place-items-center rounded-xl transition {{ isset($votes[$movie->id]) && $votes[$movie->id]['score'] > 0 ? 'bg-red-500 text-white' : 'bg-surface-elevated text-zinc-300 hover:bg-white/15 hover:text-white' }}">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 15 6-6 6 6"/></svg>
                    </button>
                    <button wire:click="vote({{ $movie->id }}, 'down')"
                            aria-label="Downvote"
                            class="grid h-11 w-12 place-items-center rounded-xl transition {{ isset($votes[$movie->id]) && $votes[$movie->id]['score'] < 0 ? 'bg-red-500 text-white' : 'bg-surface-elevated text-zinc-300 hover:bg-white/15 hover:text-white' }}">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
                    </button>
                </div>
                <div class="text-right">
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-zinc-500">Score</p>
                    <p class="text-2xl font-black text-white {{ isset($votes[$movie->id]) && $votes[$movie->id]['score'] > 0 ? 'text-green-400' : (isset($votes[$movie->id]) && $votes[$movie->id]['score'] < 0 ? 'text-red-400' : '') }}">
                        {{ $votes[$movie->id]['score'] ?? 0 }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</article>

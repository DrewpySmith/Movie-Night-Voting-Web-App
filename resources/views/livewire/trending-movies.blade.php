<div>
    @if($loading)
        <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
            @foreach(range(1, 8) as $i)
                <div class="animate-pulse">
                    <div class="aspect-[2/3] bg-surface rounded-[1.5rem] mb-3"></div>
                    <div class="h-5 bg-surface rounded-lg w-3/4 mb-1"></div>
                    <div class="h-4 bg-surface rounded-lg w-1/2"></div>
                </div>
            @endforeach
        </div>
    @elseif(empty($movies))
        <p class="text-zinc-500 text-sm">No trending movies available right now.</p>
    @else
        <div class="relative group">
            <div class="flex gap-5 overflow-x-auto pb-4 snap-x snap-mandatory scrollbar-thin">
                @foreach($movies as $movie)
                    <div class="shrink-0 w-48 snap-start group/card">
                        <div class="overflow-hidden rounded-[1.5rem] bg-surface transition duration-300 hover:-translate-y-2 hover:bg-[#202024]">
                            <div class="aspect-[2/3] overflow-hidden bg-surface-elevated relative">
                                @if($movie['poster_url'])
                                    <img src="{{ $movie['poster_url'] }}" alt="{{ $movie['title'] }}"
                                         class="h-full w-full object-cover transition duration-500 group-hover/card:scale-105"
                                         loading="lazy"
                                         onerror="this.parentElement.innerHTML = '<div class=\'flex items-center justify-center h-full text-zinc-600\'><svg class=\'w-10 h-10\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'1.5\' d=\'M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z\'/></svg></div>'">
                                @else
                                    <div class="flex items-center justify-center h-full text-zinc-600">
                                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z"/>
                                        </svg>
                                    </div>
                                @endif
                                @if($movie['rating'])
                                    <div class="absolute top-3 right-3 bg-black/70 text-yellow-300 text-xs font-bold px-2 py-0.5 rounded">
                                        {{ number_format($movie['rating'], 1) }}
                                    </div>
                                @endif
                            </div>
                            <div class="p-4">
                                <p class="text-sm font-bold text-white truncate" title="{{ $movie['title'] }}">{{ $movie['title'] }}</p>
                                @if($movie['year'])
                                    <p class="text-xs text-zinc-500 mt-1">{{ $movie['year'] }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

<x-app-layout>
    <x-slot name="title">Results - {{ $room->title }}</x-slot>

    <div class="max-w-4xl mx-auto px-5 py-12">
        @if($room->winner)
            <div class="rounded-[2rem] border border-white/10 bg-surface p-10 text-center mb-8">
                <p class="text-sm font-bold uppercase tracking-[0.35em] text-red-400">Winner!</p>
                <h3 class="mt-3 text-4xl font-black text-white">The group has decided</h3>
                <div class="mt-8 flex flex-col items-center">
                    <img src="{{ $room->winner->poster_url ?? 'https://via.placeholder.com/200x300?text=No+Poster' }}" alt="{{ $room->winner->title }}" class="w-52 h-78 object-cover rounded-2xl shadow-2xl shadow-black/30 mb-6">
                    <h4 class="text-2xl font-black text-white">{{ $room->winner->title }}</h4>
                    <p class="text-zinc-400 mt-1">{{ $room->winner->year }} &middot; {{ $room->winner->runtime }}</p>
                    @if($room->winner->imdb_rating)
                        <p class="text-yellow-300 font-bold mt-1">★ {{ $room->winner->imdb_rating }}/10</p>
                    @endif
                    <p class="text-zinc-400 mt-6 max-w-lg leading-7">{{ Str::limit($room->winner->plot, 200) }}</p>
                </div>
            </div>
        @else
            <div class="rounded-[2rem] border border-white/10 bg-surface p-8">
                <livewire:vote-area :room="$room" :key="'results-vote-' . $room->id" />
            </div>
        @endif

        <a href="{{ route('rooms.show', $room) }}" class="block w-full rounded-xl bg-white/10 py-4 text-center text-sm font-bold text-white transition hover:bg-white/15 mt-6">
            Back to Room
        </a>
    </div>
</x-app-layout>

<x-app-layout>
    <x-slot name="title">{{ $room->title }}</x-slot>

    <div class="px-5 py-8 sm:px-8 lg:px-12">

        <section class="relative overflow-hidden rounded-[2rem] border border-white/10 bg-surface p-6 sm:p-8">
            @if($room->movies->isNotEmpty() && $room->winner)
                <div class="absolute inset-0 h-full w-full object-cover opacity-20 bg-gradient-to-r from-surface via-surface/92 to-surface/70"></div>
            @endif
            <div class="relative flex flex-col justify-between gap-8 lg:flex-row lg:items-end">
                <div>
                    <p class="text-sm font-bold uppercase tracking-[0.35em] text-red-400">Room: {{ $room->title }}</p>
                    <h1 class="mt-3 text-4xl font-black tracking-tight text-white sm:text-5xl">Tonight's vote</h1>
                    <p class="mt-3 max-w-2xl text-zinc-300">
                        Hosted by {{ $room->host->name }}.
                        @if($room->invite_code)
                            Share code <span class="font-bold text-red-400">{{ $room->invite_code }}</span>
                        @endif
                        @if($room->scheduled_at)
                            before {{ $room->scheduled_at->format('M j, Y g:i A') }}.
                        @endif
                    </p>
                </div>
                @if($room->movies->isNotEmpty())
                    @php $leader = $room->movies->sortByDesc(fn($m) => $m->upvotesCount() - $m->downvotesCount())->first(); @endphp
                    @if($leader)
                        <div class="rounded-3xl border border-red-400/30 bg-red-500/10 px-5 py-4">
                            <p class="text-sm text-red-200">Current leader</p>
                            <p class="mt-1 text-xl font-black text-white">{{ $leader->title }}</p>
                        </div>
                    @endif
                @endif
            </div>
        </section>

        <div class="mt-8 grid gap-8 xl:grid-cols-[minmax(0,1fr)_420px]">
            <div class="space-y-7">
                <section class="rounded-[1.5rem] bg-surface p-5 sm:p-6">
                    <livewire:movie-search :room="$room" :key="'search-' . $room->id" />
                </section>

                <section class="grid gap-5 sm:grid-cols-2">
                    @forelse($room->movies as $movie)
                        <livewire:vote-area :room="$room" :movie="$movie" :key="'vote-' . $room->id . '-' . $movie->id" />
                    @empty
                        <div class="sm:col-span-2 rounded-[1.5rem] bg-surface p-10 text-center">
                            <p class="text-zinc-400">No movies suggested yet. Search and suggest one above!</p>
                        </div>
                    @endforelse
                </section>
            </div>

            <aside class="space-y-7">
                <section class="rounded-[1.5rem] bg-surface p-6">
                    <h2 class="text-xl font-black text-white">Members ({{ $room->members->count() }})</h2>
                    <div class="mt-5 space-y-3">
                        @foreach($room->members as $member)
                            <div class="flex items-center justify-between text-sm">
                                <span class="flex items-center gap-3 text-zinc-200">
                                    <span class="h-2.5 w-2.5 rounded-full {{ $member->id === $room->host_id ? 'bg-red-500' : 'bg-green-500' }}"></span>
                                    {{ $member->name }}
                                    @if($member->id === $room->host_id)
                                        <span class="text-xs text-red-400">host</span>
                                    @endif
                                </span>
                            </div>
                        @endforeach
                    </div>
                </section>

                <livewire:room-chat :room="$room" :key="'chat-' . $room->id" />

                @if(auth()->id() === $room->host_id)
                    <section class="rounded-[1.5rem] bg-surface p-6">
                        <h2 class="text-xl font-black text-white">Host Actions</h2>
                        <div class="mt-5 grid gap-3">
                            <a href="{{ route('rooms.edit', $room) }}" class="rounded-xl bg-white/10 px-4 py-3 text-center text-sm font-bold text-white transition hover:bg-white/15">
                                Edit Room
                            </a>

                            <form action="{{ route('rooms.invite', $room) }}" method="POST" class="space-y-2">
                                @csrf
                                <input type="email" name="email" placeholder="Email to invite..." class="w-full rounded-xl border border-white/10 bg-surface-elevated px-4 py-3 text-sm text-white outline-none transition focus:border-red-400/70">
                                <button type="submit" class="w-full rounded-xl bg-white/10 px-4 py-3 text-sm font-bold text-white transition hover:bg-white/15">
                                    Send Invite
                                </button>
                                @error('email') <p class="text-red-400 text-xs">{{ $message }}</p> @enderror
                            </form>

                            <form action="{{ route('rooms.regenerate-code', $room) }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full rounded-xl bg-white/10 px-4 py-3 text-sm font-bold text-white transition hover:bg-white/15">
                                    Regenerate Code
                                </button>
                            </form>

                            @if($room->movies->isNotEmpty())
                                <a href="{{ route('rooms.results', $room) }}" class="block w-full rounded-xl bg-red-500 px-4 py-3 text-center text-sm font-bold text-white transition hover:bg-red-400">
                                    Calculate Winner
                                </a>
                            @endif

                            @if($room->members->where('id', '!=', $room->host_id)->isNotEmpty())
                                <form action="{{ route('rooms.transfer', $room) }}" method="POST" class="space-y-2 border-t border-white/10 pt-3">
                                    @csrf
                                    <label class="text-xs font-bold uppercase tracking-wider text-zinc-500">Transfer Host</label>
                                    <select name="user_id" class="w-full rounded-xl border border-white/10 bg-surface-elevated px-4 py-3 text-sm text-white outline-none transition focus:border-red-400/70">
                                        <option value="">Select a member...</option>
                                        @foreach($room->members->where('id', '!=', $room->host_id) as $member)
                                            <option value="{{ $member->id }}">{{ $member->name }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="w-full rounded-xl bg-yellow-600/80 px-4 py-3 text-sm font-bold text-white transition hover:bg-yellow-600" onclick="return confirm('Transfer host to this member?')">
                                        Transfer Host
                                    </button>
                                    @error('transfer') <p class="text-red-400 text-xs">{{ $message }}</p> @enderror
                                </form>
                            @endif

                            <form action="{{ route('rooms.close', $room) }}" method="POST" onsubmit="return confirm('Close this room? This cannot be undone.')">
                                @csrf
                                <button type="submit" class="w-full rounded-xl bg-red-950/80 px-4 py-3 text-sm font-bold text-red-200 transition hover:bg-red-900">
                                    Close Room
                                </button>
                            </form>
                        </div>
                    </section>
                @else
                    <form action="{{ route('rooms.leave', $room) }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full rounded-xl bg-white/10 px-4 py-3 text-sm font-bold text-zinc-400 transition hover:bg-white/15" onclick="return confirm('Leave this room?')">
                            Leave Room
                        </button>
                    </form>
                @endif
            </aside>
        </div>
    </div>
</x-app-layout>

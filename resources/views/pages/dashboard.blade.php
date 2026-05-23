<x-app-layout>
    <x-slot name="title">Dashboard</x-slot>

    <div class="px-5 py-8 sm:px-8 lg:px-12">
        <div class="mb-12">
            <div class="flex items-end justify-between gap-4">
                <div>
                    <h2 class="text-3xl font-black tracking-tight text-white">Trending This Week</h2>
                    <p class="mt-2 text-zinc-400">Posters stay large, metadata is calmer, and the action is obvious.</p>
                </div>
                @if(config('services.tmdb.api_key'))
                    <span class="hidden text-sm text-zinc-500 sm:block">via TMDB images</span>
                @endif
            </div>
            <div class="mt-6">
                <livewire:trending-movies />
            </div>
        </div>

        @if($pendingInvitations->isNotEmpty())
            <div class="rounded-[1.5rem] border border-yellow-800/40 bg-surface p-6 mb-10">
                <h3 class="text-xl font-black text-white mb-4">Pending Invitations</h3>
                <div class="space-y-3">
                    @foreach($pendingInvitations as $invitation)
                        <div class="flex items-center justify-between rounded-2xl bg-surface-elevated p-4">
                            <div>
                                <p class="text-sm font-bold text-white">{{ $invitation->room->title }}</p>
                                <p class="text-xs text-zinc-400">Invited by {{ $invitation->inviter->name }}</p>
                            </div>
                            <div class="flex gap-2">
                                <a href="{{ route('invitations.accept', $invitation->token) }}" class="rounded-xl bg-green-700 px-4 py-2 text-xs font-bold text-white transition hover:bg-green-600">
                                    Accept
                                </a>
                                <form action="{{ route('invitations.decline', $invitation->token) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="rounded-xl bg-red-950/80 px-4 py-2 text-xs font-bold text-red-200 transition hover:bg-red-900">
                                        Decline
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if($myRooms->isNotEmpty())
            <h3 class="text-2xl font-black tracking-tight text-white mb-5">My Rooms</h3>
            <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3 mb-14">
                @foreach($myRooms as $room)
                    <div class="group overflow-hidden rounded-[1.5rem] bg-surface text-left transition duration-300 hover:-translate-y-2 hover:bg-[#202024]">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <span class="text-xs uppercase tracking-[0.2em] font-bold text-zinc-500">{{ $room->visibility }}</span>
                                <span class="rounded-full px-3 py-1 text-xs font-bold {{ $room->status === 'open' ? 'bg-green-900/50 text-green-300' : 'bg-zinc-800 text-zinc-400' }}">
                                    {{ $room->status }}
                                </span>
                            </div>
                            <h3 class="text-xl font-black text-white mb-2">{{ $room->title }}</h3>
                            <p class="text-sm text-zinc-400 mb-5 line-clamp-2">{{ Str::limit($room->description, 100) }}</p>
                            <div class="flex items-center justify-between text-sm text-zinc-500 mb-5">
                                <span>{{ $room->members_count ?? 0 }} members</span>
                                <span>{{ $room->movies_count ?? 0 }} movies</span>
                            </div>
                            <a href="{{ route('rooms.show', $room) }}" class="block w-full rounded-xl bg-red-500 py-3 text-center text-sm font-extrabold text-white transition hover:bg-red-400">
                                Enter Room
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <h3 class="text-2xl font-black tracking-tight text-white mb-5">Public Rooms</h3>
        @if($publicRooms->isEmpty())
            <div class="rounded-[1.5rem] bg-surface p-10 text-center">
                <p class="text-zinc-400 mb-5">No public rooms yet. Create one!</p>
                <a href="{{ route('rooms.create') }}" class="inline-block rounded-full bg-red-500 px-8 py-4 text-base font-extrabold text-white shadow-lg shadow-red-950/40 transition hover:-translate-y-1 hover:bg-red-400">
                    Create Room
                </a>
            </div>
        @else
            <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                @foreach($publicRooms as $room)
                    <div class="group overflow-hidden rounded-[1.5rem] bg-surface text-left transition duration-300 hover:-translate-y-2 hover:bg-[#202024]">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <span class="text-xs text-zinc-400">by {{ $room->host?->name }}</span>
                                <span class="rounded-full px-3 py-1 text-xs font-bold {{ $room->status === 'open' ? 'bg-green-900/50 text-green-300' : 'bg-zinc-800 text-zinc-400' }}">
                                    {{ $room->status }}
                                </span>
                            </div>
                            <h3 class="text-xl font-black text-white mb-2">{{ $room->title }}</h3>
                            <p class="text-sm text-zinc-400 mb-5 line-clamp-2">{{ Str::limit($room->description, 100) }}</p>
                            <div class="flex items-center justify-between text-sm text-zinc-500 mb-5">
                                <span>{{ $room->members_count ?? 0 }} members</span>
                                <span>{{ $room->movies_count ?? 0 }} movies</span>
                            </div>
                            <a href="{{ route('rooms.show', $room) }}" class="block w-full rounded-xl bg-white/10 py-3 text-center text-sm font-bold text-white transition hover:bg-white/15">
                                View Room
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>

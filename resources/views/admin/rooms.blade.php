<x-admin-layout>
    <x-slot name="title">Admin - Rooms</x-slot>

    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-black text-white">Rooms</h2>
        <span class="text-zinc-400 text-sm">{{ $rooms->total() }} total</span>
    </div>

    <form method="GET" action="{{ route('admin.rooms') }}" class="mb-6 flex flex-wrap gap-3">
        <div class="relative flex-1 min-w-[200px]">
            <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by title or host..." class="w-full rounded-2xl bg-surface-elevated border border-white/10 pl-11 pr-4 py-3 text-sm text-white placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-red-500/50 focus:border-red-500/50 transition">
        </div>
        <select name="status" class="rounded-2xl bg-surface-elevated border border-white/10 px-4 py-3 text-sm text-white focus:outline-none focus:ring-2 focus:ring-red-500/50">
            <option value="">All status</option>
            <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Open</option>
            <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Closed</option>
        </select>
        <select name="trashed" class="rounded-2xl bg-surface-elevated border border-white/10 px-4 py-3 text-sm text-white focus:outline-none focus:ring-2 focus:ring-red-500/50">
            <option value="">Active only</option>
            <option value="with" {{ request('trashed') === 'with' ? 'selected' : '' }}>Include deleted</option>
            <option value="only" {{ request('trashed') === 'only' ? 'selected' : '' }}>Deleted only</option>
        </select>
        <button type="submit" class="rounded-2xl bg-red-500 px-5 py-3 text-sm font-bold text-white transition hover:bg-red-400">Filter</button>
        @if(request()->anyFilled(['search', 'status', 'trashed']))
            <a href="{{ route('admin.rooms') }}" class="rounded-2xl bg-zinc-800 px-5 py-3 text-sm font-bold text-zinc-300 transition hover:bg-zinc-700">Clear</a>
        @endif
    </form>

    <div class="rounded-[1.5rem] bg-surface overflow-hidden">
        <table class="w-full">
            <thead>
                <tr class="border-b border-white/10 text-left text-sm text-zinc-500">
                    <th class="px-6 py-4 font-bold uppercase tracking-[0.1em]">Title</th>
                    <th class="px-6 py-4 font-bold uppercase tracking-[0.1em]">Host</th>
                    <th class="px-6 py-4 font-bold uppercase tracking-[0.1em]">Status</th>
                    <th class="px-6 py-4 font-bold uppercase tracking-[0.1em]">Members</th>
                    <th class="px-6 py-4 font-bold uppercase tracking-[0.1em]">Movies</th>
                    <th class="px-6 py-4 font-bold uppercase tracking-[0.1em]">Votes</th>
                    <th class="px-6 py-4 font-bold uppercase tracking-[0.1em]">Created</th>
                    <th class="px-6 py-4"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/10">
                @foreach($rooms as $room)
                    <tr class="text-sm text-zinc-300 hover:bg-white/5 transition {{ $room->trashed() ? 'opacity-60' : '' }}">
                        <td class="px-6 py-4 font-bold text-white">{{ $room->title }}</td>
                        <td class="px-6 py-4">{{ $room->host?->name ?? 'Deleted' }}</td>
                        <td class="px-6 py-4">
                            @if($room->trashed())
                                <span class="text-xs font-bold px-3 py-1 rounded-full bg-red-900/50 text-red-400">deleted</span>
                            @else
                                <span class="text-xs font-bold px-3 py-1 rounded-full {{ $room->status === 'open' ? 'bg-green-900/50 text-green-300' : 'bg-zinc-800 text-zinc-400' }}">
                                    {{ $room->status }}
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4">{{ $room->members_count }}</td>
                        <td class="px-6 py-4">{{ $room->movies_count }}</td>
                        <td class="px-6 py-4">{{ $room->votes_count }}</td>
                        <td class="px-6 py-4 text-zinc-500">{{ $room->created_at->format('M j, Y') }}</td>
                        <td class="px-6 py-4 text-right">
                            @if($room->trashed())
                                <form action="{{ route('admin.rooms.restore', $room->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="text-green-400 hover:text-green-300 text-xs font-bold transition">Restore</button>
                                </form>
                            @else
                                <form action="{{ route('admin.rooms.delete', $room) }}" method="POST" onsubmit="return confirm('Delete this room?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-400 hover:text-red-300 text-xs font-bold transition">Delete</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $rooms->links() }}
    </div>
</x-admin-layout>

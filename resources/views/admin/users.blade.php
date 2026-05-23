<x-admin-layout>
    <x-slot name="title">Admin - Users</x-slot>

    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-black text-white">Users</h2>
        <span class="text-zinc-400 text-sm">{{ $users->total() }} total</span>
    </div>

    <form method="GET" action="{{ route('admin.users') }}" class="mb-6">
        <div class="relative max-w-md">
            <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or email..." class="w-full rounded-2xl bg-surface-elevated border border-white/10 pl-11 pr-4 py-3 text-sm text-white placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-red-500/50 focus:border-red-500/50 transition">
        </div>
    </form>

    <div class="rounded-[1.5rem] bg-surface overflow-hidden">
        <table class="w-full">
            <thead>
                <tr class="border-b border-white/10 text-left text-sm text-zinc-500">
                    <th class="px-6 py-4 font-bold uppercase tracking-[0.1em]">Name</th>
                    <th class="px-6 py-4 font-bold uppercase tracking-[0.1em]">Email</th>
                    <th class="px-6 py-4 font-bold uppercase tracking-[0.1em]">Role</th>
                    <th class="px-6 py-4 font-bold uppercase tracking-[0.1em]">Rooms</th>
                    <th class="px-6 py-4 font-bold uppercase tracking-[0.1em]">Joined</th>
                    <th class="px-6 py-4"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/10">
                @foreach($users as $user)
                    <tr class="text-sm text-zinc-300 hover:bg-white/5 transition">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-surface-elevated flex items-center justify-center text-xs text-white font-bold">{{ substr($user->name, 0, 2) }}</div>
                                <span class="font-bold text-white">{{ $user->name }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">{{ $user->email }}</td>
                        <td class="px-6 py-4">
                            @if($user->is_admin)
                                <span class="text-xs font-bold px-3 py-1 rounded-full bg-red-500/15 text-red-300">Admin</span>
                            @else
                                <span class="text-xs font-bold px-3 py-1 rounded-full bg-zinc-800 text-zinc-400">User</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">{{ $user->hosted_rooms_count }}</td>
                        <td class="px-6 py-4 text-zinc-500">{{ $user->created_at->format('M j, Y') }}</td>
                        <td class="px-6 py-4 text-right">
                            <form action="{{ route('admin.users.delete', $user) }}" method="POST" onsubmit="return confirm('Delete this user?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-400 hover:text-red-300 text-xs font-bold transition">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $users->links() }}
    </div>
</x-admin-layout>

<x-admin-layout>
    <x-slot name="title">Admin Dashboard</x-slot>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 mb-8">
        <div class="rounded-[1.5rem] bg-surface p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-zinc-400 text-sm font-bold uppercase tracking-[0.1em]">Total Users</p>
                    <p class="text-3xl font-black text-white mt-1">{{ $stats['users'] }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-900/30 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </div>
            </div>
        </div>

        <div class="rounded-[1.5rem] bg-surface p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-zinc-400 text-sm font-bold uppercase tracking-[0.1em]">Total Rooms</p>
                    <p class="text-3xl font-black text-white mt-1">{{ $stats['rooms'] }}</p>
                </div>
                <div class="w-12 h-12 bg-green-900/30 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z"/></svg>
                </div>
            </div>
        </div>

        <div class="rounded-[1.5rem] bg-surface p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-zinc-400 text-sm font-bold uppercase tracking-[0.1em]">Open Rooms</p>
                    <p class="text-3xl font-black text-white mt-1">{{ $stats['open_rooms'] }}</p>
                </div>
                <div class="w-12 h-12 bg-yellow-900/30 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
        </div>

        <div class="rounded-[1.5rem] bg-surface p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-zinc-400 text-sm font-bold uppercase tracking-[0.1em]">Comments</p>
                    <p class="text-3xl font-black text-white mt-1">{{ $stats['comments'] }}</p>
                </div>
                <div class="w-12 h-12 bg-purple-900/30 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                </div>
            </div>
        </div>

        <div class="rounded-[1.5rem] bg-surface p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-zinc-400 text-sm font-bold uppercase tracking-[0.1em]">Deleted Rooms</p>
                    <p class="text-3xl font-black text-red-400 mt-1">{{ $stats['deleted_rooms'] }}</p>
                </div>
                <div class="w-12 h-12 bg-red-900/30 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-8">
        <div class="rounded-[1.5rem] bg-surface p-6">
            <h3 class="text-xl font-black text-white mb-4">Recent Users</h3>
            <div class="space-y-3">
                @forelse($recentUsers as $user)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-surface-elevated flex items-center justify-center text-xs text-white font-bold">{{ substr($user->name, 0, 2) }}</div>
                            <div>
                                <p class="text-sm font-bold text-white">{{ $user->name }}</p>
                                <p class="text-xs text-zinc-400">{{ $user->email }}</p>
                            </div>
                        </div>
                        <span class="text-xs text-zinc-500">{{ $user->created_at->diffForHumans() }}</span>
                    </div>
                @empty
                    <p class="text-zinc-400 text-sm">No users yet</p>
                @endforelse
            </div>
        </div>

        <div class="rounded-[1.5rem] bg-surface p-6">
            <h3 class="text-xl font-black text-white mb-4">Recent Rooms</h3>
            <div class="space-y-3">
                @forelse($recentRooms as $room)
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-bold text-white">{{ $room->title }}</p>
                            <p class="text-xs text-zinc-400">by {{ $room->host?->name ?? 'Deleted' }} &middot; {{ $room->members_count ?? 0 }} members</p>
                        </div>
                        <span class="text-xs font-bold px-3 py-1 rounded-full {{ $room->status === 'open' ? 'bg-green-900/50 text-green-300' : 'bg-zinc-800 text-zinc-400' }}">{{ $room->status }}</span>
                    </div>
                @empty
                    <p class="text-zinc-400 text-sm">No rooms yet</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="rounded-[1.5rem] bg-surface p-6">
        <h3 class="text-xl font-black text-white mb-4">Room Activity</h3>
        <canvas id="roomChart" height="100"></canvas>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('roomChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: @json($chartLabels),
                    datasets: [{
                        label: 'Rooms Created',
                        data: @json($chartData),
                        backgroundColor: '#ef4444',
                        borderRadius: 4,
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { labels: { color: '#a1a1aa' } }
                    },
                    scales: {
                        x: { ticks: { color: '#a1a1aa' }, grid: { color: '#27272a' } },
                        y: { ticks: { color: '#a1a1aa' }, grid: { color: '#27272a' }, beginAtZero: true }
                    }
                }
            });
        });
    </script>
    @endpush
</x-admin-layout>

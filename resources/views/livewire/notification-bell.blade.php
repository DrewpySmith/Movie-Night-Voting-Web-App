<div x-data="{ open: false }" class="relative">
    <button @click="open = !open; $wire.refreshNotifications()"
            class="relative flex h-9 w-9 items-center justify-center rounded-full text-zinc-400 transition hover:bg-white/5 hover:text-white">
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
            <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
        </svg>
        @if($unreadCount > 0)
            <span class="absolute -right-0.5 -top-0.5 flex h-4 min-w-[16px] items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold leading-none text-white">
                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
            </span>
        @endif
    </button>

    <div x-show="open" x-cloak @click.outside="open = false"
         class="absolute right-0 top-10 z-50 w-80 rounded-2xl border border-white/10 bg-surface shadow-2xl shadow-black/50">
        <div class="flex items-center justify-between border-b border-white/10 px-4 py-3">
            <span class="text-sm font-bold text-zinc-200">Notifications</span>
            @if($unreadCount > 0)
                <button wire:click="markAllAsRead" class="text-xs font-semibold text-red-400 transition hover:text-red-300">
                    Mark all read
                </button>
            @endif
        </div>

        <div class="max-h-80 overflow-y-auto">
            @forelse($notifications as $n)
                <a href="{{ $n['action_url'] }}"
                   @click="open = false"
                   wire:click="markAsRead('{{ $n['id'] }}')"
                   class="flex items-start gap-3 border-b border-white/5 px-4 py-3 transition hover:bg-white/5 {{ !$n['read'] ? 'bg-white/[0.02]' : '' }}">
                    <div class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-red-500/10">
                        @switch($n['type'])
                            @case('vote_received')
                                <svg class="h-3.5 w-3.5 text-red-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m12 20 5-8H7l5 8Z"/><path d="M12 4 7 12h10L12 4Z"/></svg>
                                @break
                            @case('new_member_joined')
                                <svg class="h-3.5 w-3.5 text-red-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" x2="22" y1="8" y2="8"/><line x1="20" y1="6" x2="20" y2="10"/></svg>
                                @break
                            @case('invitation_created')
                                <svg class="h-3.5 w-3.5 text-red-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                                @break
                            @case('invitation_accepted')
                                <svg class="h-3.5 w-3.5 text-red-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                @break
                            @case('winner_declared')
                                <svg class="h-3.5 w-3.5 text-yellow-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="6"/><path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"/></svg>
                                @break
                            @default
                                <svg class="h-3.5 w-3.5 text-red-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        @endswitch
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm leading-snug text-zinc-300 {{ !$n['read'] ? 'font-semibold' : '' }}">
                            {{ $n['message'] }}
                        </p>
                        <p class="mt-0.5 text-xs text-zinc-500">{{ $n['created_at'] }}</p>
                    </div>
                </a>
            @empty
                <div class="px-4 py-8 text-center">
                    <svg class="mx-auto mb-2 h-8 w-8 text-zinc-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                    </svg>
                    <p class="text-sm text-zinc-500">No notifications yet</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

@auth
<div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false" class="fixed inset-0 bg-black/60 z-30 md:hidden"></div>

<aside class="fixed inset-y-0 left-0 z-40 hidden flex-col border-r border-white/10 bg-surface-black/95 backdrop-blur transition-all duration-300 md:flex"
       :class="$store.sidebar.expanded ? 'w-60' : 'w-20'">
    <div class="flex h-[72px] w-full items-center border-b border-white/10">
        <a href="{{ route('dashboard') }}" class="flex flex-1 items-center justify-center gap-2 text-xl font-black tracking-tight text-red-500">
            <span>MN</span>
            <span x-show="$store.sidebar.expanded" x-cloak class="text-sm">MENU</span>
        </a>
        <button @click="$store.sidebar.toggle()" class="mr-3 text-zinc-500 hover:text-white transition">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg>
        </button>
    </div>
    <nav class="mt-5 flex flex-1 flex-col items-center gap-1" :class="$store.sidebar.expanded ? 'px-4' : 'px-3'">
        <a href="{{ route('dashboard') }}"
           class="flex items-center gap-3 rounded-2xl transition duration-300"
           :class="[
               $store.sidebar.expanded ? 'w-full px-4 py-3 justify-start' : 'h-12 w-12 justify-center',
               {{ request()->routeIs('dashboard') ? "'bg-red-500 text-white shadow-lg shadow-red-950/50'" : "'text-zinc-500 hover:bg-white/5 hover:text-white'" }}
           ]">
            <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 11 9-8 9 8"/><path d="M5 10v10h14V10"/><path d="M9 20v-6h6v6"/></svg>
            <span x-show="$store.sidebar.expanded" x-cloak class="text-sm font-bold">Home</span>
        </a>
        <a href="{{ route('rooms.create') }}"
           class="flex items-center gap-3 rounded-2xl transition duration-300"
           :class="[
               $store.sidebar.expanded ? 'w-full px-4 py-3 justify-start' : 'h-12 w-12 justify-center',
               {{ request()->routeIs('rooms.create*') ? "'bg-red-500 text-white shadow-lg shadow-red-950/50'" : "'text-zinc-500 hover:bg-white/5 hover:text-white'" }}
           ]">
            <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 4v16m8-8H4"/></svg>
            <span x-show="$store.sidebar.expanded" x-cloak class="text-sm font-bold">New Room</span>
        </a>
        @if(Auth::user()->is_admin)
            <a href="{{ route('admin.dashboard') }}"
               class="flex items-center gap-3 rounded-2xl transition duration-300"
               :class="[
                   $store.sidebar.expanded ? 'w-full px-4 py-3 justify-start' : 'h-12 w-12 justify-center',
                   {{ request()->routeIs('admin.*') ? "'bg-red-500 text-white shadow-lg shadow-red-950/50'" : "'text-zinc-500 hover:bg-white/5 hover:text-white'" }}
               ]">
                <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                <span x-show="$store.sidebar.expanded" x-cloak class="text-sm font-bold">Admin</span>
            </a>
        @endif
    </nav>
    <div class="mt-auto mb-5 flex flex-col items-center gap-1" :class="$store.sidebar.expanded ? 'px-4' : 'px-3'">
        <a href="{{ route('profile.show') }}"
           class="flex items-center gap-3 rounded-2xl transition"
           :class="[
               $store.sidebar.expanded ? 'w-full px-4 py-3 justify-start' : 'h-12 w-12 justify-center',
               {{ request()->routeIs('profile.*') ? "'bg-white/10 text-white'" : "'text-zinc-400 hover:bg-white/10 hover:text-white'" }}
           ]">
            @if(Auth::user()->avatar)
                <img src="{{ asset('storage/' . Auth::user()->avatar) }}" alt="" class="h-7 w-7 shrink-0 rounded-full object-cover">
            @else
                <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21a8 8 0 0 0-16 0"/><circle cx="12" cy="7" r="4"/></svg>
            @endif
            <span x-show="$store.sidebar.expanded" x-cloak class="text-sm font-bold text-zinc-300">Profile</span>
        </a>
        <form method="POST" action="{{ route('logout') }}" class="w-full">
            @csrf
            <button type="submit"
               class="flex items-center gap-3 rounded-2xl transition hover:bg-white/5 hover:text-red-400"
               :class="$store.sidebar.expanded ? 'w-full px-4 py-3 justify-start text-zinc-500' : 'h-12 w-12 justify-center text-zinc-500'">
                <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                <span x-show="$store.sidebar.expanded" x-cloak class="text-sm font-bold">Logout</span>
            </button>
        </form>
    </div>
    <button @click="$store.sidebar.toggle()"
            class="absolute -right-3 top-20 z-50 hidden h-6 w-6 items-center justify-center rounded-full border border-white/10 bg-surface text-zinc-400 transition hover:text-white md:flex">
        <svg class="h-3 w-3 transition" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"
             :class="$store.sidebar.expanded ? 'rotate-0' : 'rotate-180'">
            <path d="m15 18-6-6 6-6"/>
        </svg>
    </button>
</aside>

<div class="w-full transition-all duration-300" :class="$store.sidebar.expanded ? 'md:pl-60' : 'md:pl-20'">
    <header class="sticky top-0 z-30 flex h-[72px] items-center justify-between border-b border-white/10 bg-surface-black/90 px-5 backdrop-blur-xl sm:px-8 lg:px-12">
        <button @click="sidebarOpen = true" class="md:hidden text-zinc-400 hover:text-white">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
        <a href="{{ route('dashboard') }}" class="text-lg font-black tracking-tight text-red-500 sm:text-xl">MOVIE NIGHT</a>
        <div class="flex items-center gap-3">
            <livewire:notification-bell :key="'bell-' . auth()->id()" />
            <a href="{{ route('rooms.create') }}" class="rounded-full bg-red-500 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-red-950/40 transition hover:-translate-y-0.5 hover:bg-red-400">
                Open Room
            </a>
        </div>
    </header>
</div>
@endauth

@guest
<header class="sticky top-0 z-30 flex h-[72px] items-center justify-between border-b border-white/10 bg-surface-black/90 px-5 backdrop-blur-xl sm:px-8 lg:px-12">
    <a href="{{ route('dashboard') }}" class="text-lg font-black tracking-tight text-red-500 sm:text-xl">MOVIE NIGHT</a>
    <div class="flex items-center gap-3">
        <a href="{{ route('login') }}" class="rounded-full px-4 py-2 text-sm font-semibold text-zinc-300 transition hover:text-white">Sign In</a>
        <a href="{{ route('register') }}" class="rounded-full bg-red-500 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-red-950/40 transition hover:-translate-y-0.5 hover:bg-red-400">Get Started</a>
    </div>
</header>
@endguest
